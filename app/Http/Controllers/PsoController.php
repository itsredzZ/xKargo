<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\SimulationResult;
use App\Models\CarryoverItem;
use App\Models\RelocationLog;
use App\Models\Truck;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PsoController extends Controller
{
    /**
     * Halaman Utama Optimasi & Hasil (Menampilkan Blade)
     */
    public function results()
    {
        // 1. Ambil item yang statusnya 'pending' untuk hari ini (dari input pengiriman)
        $itemsHariIni = Item::whereDate('created_at', Carbon::today())
                             ->where('status', 'pending')
                             ->with('cityOrigin', 'cityDestination')
                             ->get();

        // 2. Ambil data truk yang aktif untuk dikirim ke JS (misal buat mapping)
        $trucks = Truck::where('is_active', true)->get();

        return view('pso.results', compact('itemsHariIni', 'trucks'));
    }

    /**
     * API Endpoint: Jalankan Python PSO via AJAX
     */
    public function run(Request $request)
    {
        $itemIds = $request->input('item_ids', []);

        // 1. Ambil items yang status ordernya 'pending' hari ini
        $orders = DeliveryOrder::whereDate('order_date', Carbon::today())
                    ->where('status', 'pending')
                    ->whereIn('item_id', $itemIds)
                    ->with(['item', 'originDepot', 'destinationCity'])
                    ->get();

        if ($orders->isEmpty()) {
            return response()->json(['error' => 'Tidak ada pesanan pending yang dipilih'], 400);
        }

        // 2. Susun data mentah untuk dikirim ke Python
        $rawItems = [];
        foreach ($orders as $order) {
            $rawItems[] = [
                'id'            => $order->item->id,
                'nama'          => $order->item->name,
                'panjang'       => (float) $order->item->length_cm,
                'lebar'         => (float) $order->item->width_cm,
                'tinggi'        => (float) $order->item->height_cm,
                'berat_fisik'   => (float) $order->item->weight_kg,
                'kota_asal'     => $order->originDepot->name,
                'kota_tujuan'   => $order->destinationCity->name,
                'is_carryover'  => (bool) $order->item->is_carryover,
            ];
        }

        // 3. Ambil data Truk Aktif
        $trucksData = Truck::where('is_active', true)->with('homeDepot')->get()->map(function($t) {
            return [
                'id' => $t->id,
                'plate_number' => $t->plate_number,
                'max_weight_kg' => (float) $t->max_weight_kg,
                'box_p' => (float) $t->length_cm,
                'box_l' => (float) $t->width_cm,
                'box_t' => (float) $t->height_cm,
                'depot_asal' => $t->homeDepot->name,
            ];
        })->toArray();

        // 4. Bangun Graph Jalan (Sama seperti konsep sebelumnya)
        $cities = City::where('is_active', true)->pluck('name')->toArray();
        $cityIdx = array_flip($cities);
        
        $coordsRaw = City::where('is_active', true)->get();
        $coords = [];
        foreach ($coordsRaw as $c) {
            $coords[$c->name] = [(float) $c->latitude, (float) $c->longitude];
        }

        $n = count($cities);
        $adj = array_fill(0, $n, array_fill(0, $n, float('inf')));
        for ($i=0; $i < $n; $i++) $adj[$i][$i] = 0.0;
        
        $distances = DB::table('depot_distances')->get();
        foreach ($distances as $d) {
            $i = array_search($cities[$d->city_a_id - 1] ?? '', $cities); // asumsi ID urut, lebih aman pakai query join jika tidak
            // Lebih aman cari berdasarkan nama kota dari relasi:
            $cityA = City::find($d->city_a_id)->name ?? null;
            $cityB = City::find($d->city_b_id)->name ?? null;
            if($cityA && $cityB) {
                $i = $cityIdx[$cityA];
                $j = $cityIdx[$cityB];
                $adj[$i][$j] = (float) $d->distance_km;
                $adj[$j][$i] = (float) $d->distance_km;
            }
        }

        $depotNames = City::where('is_depot', 1)->pluck('name')->toArray();

        // 5. Ambil Settings
        $psoSettings = Setting::where('param_group', 'pso')->pluck('param_value', 'param_key')->toArray();
        $opSettings = Setting::where('param_group', 'operasional')->pluck('param_value', 'param_key')->toArray();

        // 6. Kumpulkan Payload
        $payload = [
            'items' => $rawItems,
            'trucks' => $trucksData,
            'graph' => [
                'cities' => $cities,
                'adj' => $adj,
                'coords' => $coords,
                'depot_names' => $depotNames
            ],
            'pso_params' => $psoSettings,
            'op_params' => $opSettings
        ];

        // 7. Eksekusi Python
        $pythonPath = "C:/laragon/bin/python/python.exe"; // Path kamu
        $scriptPath = base_path('engine/run_pso.py'); 

        $command = escapeshellcmd("$pythonPath $scriptPath " . escapeshellarg(json_encode($payload)));
        $output = shell_exec($command . " 2>&1");

        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Python Error: ' . $output], 500);
        }

        session(['hasil_pso_temp' => $result]);
        return response()->json($result);
    }

    /**
     * Simpan Hasil Optimasi ke Database
     */
    public function save(Request $request)
    {
        $hasil = session('hasil_pso_temp');
        if (!$hasil) {
            return redirect()->back()->withErrors('Tidak ada hasil optimasi untuk disimpan.');
        }

        DB::beginTransaction();
        try {
            $today = Carbon::today();

            // 1. Simpan hasil per truk (SimulationResult)
            foreach ($hasil['best_routes'] as $truckId => $routeData) {
                SimulationResult::create([
                    'run_date' => $today,
                    'truck_id' => $truckId,
                    'route_json' => json_encode($routeData['rute']),
                    'items_json' => json_encode($routeData['items']),
                    'total_weight_kg' => $routeData['berat_muatan'],
                    'tariff_total' => $routeData['tarif'],
                    'fuel_cost' => $routeData['biaya_bbm'],
                    'net_profit' => $routeData['tarif'] - $routeData['biaya_bbm'],
                    'gbest_curve_json' => json_encode($hasil['gbest_curve']),
                ]);

                // Update status item jadi "terkirim"
                foreach ($routeData['items'] as $item) {
                    Item::where('id', $item['id'])->update(['status' => 'terkirim']);
                }
            }

            // 2. Simpan Carry Over
            if (isset($hasil['carryover_items'])) {
                foreach ($hasil['carryover_items'] as $coItem) {
                    Item::where('id', $coItem['id'])->update(['status' => 'carryover']);
                    CarryoverItem::create([
                        'item_id' => $coItem['id'],
                        'carryover_date' => $today,
                        'reason' => $coItem['alasan_carryover'] ?? 'guillotine_gagal',
                        'resolved' => false
                    ]);
                }
            }

            // 3. Simpan Relokasi Truk
            if (isset($hasil['relokasi'])) {
                foreach ($hasil['relokasi'] as $relok) {
                    // Logic serupa dengan streamlit mu...
                }
            }

            // 4. Update posisi truk saat ini
            if (isset($hasil['truck_akhir'])) {
                foreach ($hasil['truck_akhir'] as $truckId => $kotaNama) {
                    $kota = City::where('name', $kotaNama)->first();
                    if ($kota) {
                        Truck::where('id', $truckId)->update(['current_city_id' => $kota->id]);
                    }
                }
            }

            DB::commit();
            session()->forget('hasil_pso_temp');
            
            return redirect()->route('pso.results')->with('success', 'Hasil optimasi berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal menyimpan: ' . $e->getMessage());
        }
    }
}