<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\DeliveryOrder;
use App\Models\SimulationResult;
use App\Models\CarryoverItem;
use App\Models\RelocationLog;
use App\Models\Truck;
use App\Models\City;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PsoController extends Controller
{
    // Halaman Input Pesanan (Sementara, untuk testing)
    public function orders()
    {
        $depots = City::where('is_depot', 1)->where('is_active', 1)->get();
        $allCities = City::where('is_active', 1)->get();
        $todayOrders = DeliveryOrder::whereDate('order_date', Carbon::today())
            ->where('status', 'pending')
            ->with(['items', 'originDepot', 'destinationCity'])
            ->orderBy('id', 'desc')->get();

        return view('pso.orders', compact('depots', 'allCities', 'todayOrders'));
    }

    // Simpan Pesanan Baru
    public function storeOrder(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'length_cm' => 'required|numeric|min:1',
            'width_cm' => 'required|numeric|min:1',
            'height_cm' => 'required|numeric|min:1',
            'weight_kg' => 'required|numeric|min:0.1',
            'origin_depot_id' => 'required|exists:cities,id',
            'destination_city_id' => 'required|exists:cities,id',
        ]);

        $order = DeliveryOrder::create([
            'origin_depot_id' => $request->origin_depot_id,
            'destination_city_id' => $request->destination_city_id,
            'order_date' => Carbon::today(),
            'status' => 'pending',
            'source' => 'manual',
        ]);

        Item::create([
            'order_id' => $order->id,
            'name' => $request->name,
            'length_cm' => $request->length_cm,
            'width_cm' => $request->width_cm,
            'height_cm' => $request->height_cm,
            'weight_kg' => $request->weight_kg,
            'status' => 'menunggu',
            'is_carryover' => false,
        ]);

        return redirect()->route('pso.orders')->with('success', 'Barang berhasil ditambahkan!');
    }

    // Endpoint API untuk ambil data items
    public function items()
    {
        $items = Item::where('status', 'menunggu')
            ->whereHas('deliveryOrder', fn($q) => $q->whereDate('order_date', Carbon::today())->where('status', 'pending'))
            ->with('deliveryOrder.originDepot', 'deliveryOrder.destinationCity')
            ->get()->map(fn($item) => [
                'id' => $item->id,
                'nama' => $item->name,
                'panjang' => (float) $item->length_cm,
                'lebar' => (float) $item->width_cm,
                'tinggi' => (float) $item->height_cm,
                'berat_fisik' => (float) $item->weight_kg,
                'kota_asal' => $item->deliveryOrder->originDepot->name ?? '-',
                'kota_tujuan' => $item->deliveryOrder->destinationCity->name ?? '-',
                'is_carryover' => (bool) $item->is_carryover,
            ]);
        return response()->json($items);
    }

    // Halaman Optimasi & Hasil PSO
    public function results()
    {
        return view('pso.results');
    }

    // Jalankan Python PSO
    public function run(Request $request)
    {
        // 1. Gather items from DB (Sekarang memakai relasi yang benar: item -> deliveryOrder)
        $dbItems = Item::where('status', 'menunggu')
            ->whereHas('deliveryOrder', fn($q) => $q->whereDate('order_date', Carbon::today())->where('status', 'pending'))
            ->with('deliveryOrder.originDepot', 'deliveryOrder.destinationCity')
            ->get();

        $rawItems = [];
        foreach ($dbItems as $item) {
            $rawItems[] = [
                'id' => $item->id,
                'nama' => $item->name,
                'panjang' => (float) $item->length_cm,
                'lebar' => (float) $item->width_cm,
                'tinggi' => (float) $item->height_cm,
                'berat_fisik' => (float) $item->weight_kg,
                'kota_asal' => $item->deliveryOrder->originDepot->name ?? 'Unknown',
                'kota_tujuan' => $item->deliveryOrder->destinationCity->name ?? 'Unknown',
                'is_carryover' => (bool) $item->is_carryover,
            ];
        }

        if (empty($rawItems)) return response()->json(['error' => 'Tidak ada pesanan pending hari ini'], 400);

        // 2. Gather Trucks
        $trucksData = Truck::where('is_active', true)->with('homeDepot')->get()->map(fn($t) => [
            'id' => $t->id,
            'plate_number' => $t->plate_number,
            'max_weight_kg' => (float) $t->max_weight_kg,
            'box_p' => (float) $t->length_cm,
            'box_l' => (float) $t->width_cm,
            'box_t' => (float) $t->height_cm,
            'depot_asal' => $t->homeDepot->name ?? 'Unknown',
        ])->toArray();

        // 3. Build Graph
        $cities = City::where('is_active', true)->pluck('name')->toArray();
        $cityIdx = array_flip($cities);
        $coords = City::where('is_active', true)->get()->mapWithKeys(fn($c) => [$c->name => [(float) $c->latitude, (float) $c->longitude]])->toArray();
        
        $n = count($cities);
        $adj = array_fill(0, $n, array_fill(0, $n, float('inf')));
        for ($i=0; $i<$n; $i++) $adj[$i][$i] = 0.0;
        
        foreach (DB::table('depot_distances')->get() as $d) {
            $cA = City::find($d->city_a_id)->name ?? null;
            $cB = City::find($d->city_b_id)->name ?? null;
            if ($cA && $cB && isset($cityIdx[$cA]) && isset($cityIdx[$cB])) {
                $adj[$cityIdx[$cA]][$cityIdx[$cB]] = (float) $d->distance_km;
            }
        }
        $depotNames = City::where('is_depot', 1)->pluck('name')->toArray();

        // 4. Settings
        $psoSettings = Setting::where('param_group', 'pso')->pluck('param_value', 'param_key')->toArray();
        $opSettings = Setting::where('param_group', 'operasional')->pluck('param_value', 'param_key')->toArray();

        // 5. Execute Python
        $payload = json_encode([
            'items' => $rawItems, 'trucks' => $trucksData,
            'graph' => ['cities' => $cities, 'adj' => $adj, 'coords' => $coords, 'depot_names' => $depotNames],
            'pso_params' => $psoSettings, 'op_params' => $opSettings
        ]);

        $pythonPath = "C:/laragon/bin/python/python.exe";
        $command = escapeshellcmd("$pythonPath " . base_path('engine/run_pso.py') . " " . escapeshellarg($payload));
        $output = shell_exec($command . " 2>&1");

        $result = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) return response()->json(['error' => 'Python Error: ' . $output], 500);

        session(['hasil_pso_temp' => $result]);
        return response()->json($result);
    }

    // Simpan ke DB
    public function save(Request $request)
    {
        $hasil = session('hasil_pso_temp');
        if (!$hasil) return redirect()->back()->withErrors('Tidak ada hasil optimasi.');
        
        DB::beginTransaction();
        try {
            $today = Carbon::today();
            foreach ($hasil['best_routes'] as $truckId => $ri) {
                SimulationResult::create([
                    'run_date' => $today, 'truck_id' => $truckId,
                    'route_json' => ['rute' => $ri['rute']], 
                    'total_weight_kg' => $ri['berat_muatan'],
                    'tariff_total' => $ri['tarif'], 'fuel_cost' => $ri['biaya_bbm'],
                    'net_profit' => $ri['tarif'] - $ri['biaya_bbm'],
                    'gbest_curve_json' => $hasil['gbest_curve'],
                ]);
                // Update status item jadi terkirim
                foreach ($ri['items'] as $it) {
                    Item::where('id', $it['id'])->update(['status' => 'terkirim']);
                }
            }
            // Update status order jadi selesai (opsional)
            DeliveryOrder::whereDate('order_date', $today)->update(['status' => 'selesai']);
            
            DB::commit();
            session()->forget('hasil_pso_temp');
            return redirect()->route('pso.results')->with('success', 'Hasil optimasi tersimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal simpan: ' . $e->getMessage());
        }
    }
}