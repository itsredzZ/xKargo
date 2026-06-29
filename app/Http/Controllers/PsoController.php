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
    // Halaman Input Pesanan
    public function orders()
    {
        $depots    = City::where('is_depot', 1)->where('is_active', 1)->get();
        $allCities = City::where('is_active', 1)->get();

        $todayOrders = DeliveryOrder::whereDate('order_date', Carbon::today())
            ->where('status', 'pending')
            ->with(['items', 'originDepot', 'destinationCity'])
            ->orderBy('id', 'desc')
            ->get();

        // Carry-over: barang is_carryover=true & status=menunggu dari pesanan pending
        // (bisa berasal dari hari-hari sebelumnya yang belum muat di truk)
        $carryoverItems = Item::where('is_carryover', true)
            ->where('status', 'menunggu')
            ->whereHas('deliveryOrder', fn($q) => $q->where('status', 'pending'))
            ->with(['deliveryOrder.originDepot', 'deliveryOrder.destinationCity'])
            ->get();

        return view('pso.orders', compact('depots', 'allCities', 'todayOrders', 'carryoverItems'));
    }

    // Simpan Pesanan Baru dari Form Manual
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string',
            'length_cm'             => 'required|numeric|min:1',
            'width_cm'              => 'required|numeric|min:1',
            'height_cm'             => 'required|numeric|min:1',
            'weight_kg'             => 'required|numeric|min:0.1',
            'origin_depot_id'       => 'required|exists:cities,id',
            'destination_city_id'   => 'required|exists:cities,id',
        ]);

        $order = DeliveryOrder::create([
            'origin_depot_id'       => $request->origin_depot_id,
            'destination_city_id'   => $request->destination_city_id,
            'order_date'            => Carbon::today(),
            'status'                => 'pending',
            'source'                => 'manual',
        ]);

        Item::create([
            'order_id'      => $order->id,
            'name'          => $request->name,
            'length_cm'     => $request->length_cm,
            'width_cm'      => $request->width_cm,
            'height_cm'     => $request->height_cm,
            'weight_kg'     => $request->weight_kg,
            'status'        => 'menunggu',
            'is_carryover'  => false,
        ]);

        return redirect()->route('pso.orders')->with('success', 'Barang berhasil ditambahkan!');
    }

    // Import pesanan dari file Excel
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            // Tidak ada lagi origin_depot_id / destination_city_id global —
            // depot & kota tujuan dibaca per-baris dari kolom G & H
        ]);

        $file = $request->file('excel_file');
        $rawRows = [];

        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            $data  = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
            $sheet = $data[0];
            foreach (array_slice($sheet, 1) as $row) {
                if (empty($row[0])) continue;
                $rawRows[] = [
                    'name'        => $row[0],
                    'weight_kg'   => $row[1] ?? 0,
                    'length_cm'   => $row[2] ?? 0,
                    'width_cm'    => $row[3] ?? 0,
                    'height_cm'   => $row[4] ?? 0,
                    'quantity'    => $row[5] ?? 1,
                    'depot_asal'  => trim($row[6] ?? ''),
                    'kota_tujuan' => trim($row[7] ?? ''),
                ];
            }
        } else {
            $csv = array_map('str_getcsv', file($file->getRealPath()));
            array_shift($csv);
            foreach ($csv as $row) {
                if (empty($row[0])) continue;
                $rawRows[] = [
                    'name'        => $row[0],
                    'weight_kg'   => $row[1] ?? 0,
                    'length_cm'   => $row[2] ?? 0,
                    'width_cm'    => $row[3] ?? 0,
                    'height_cm'   => $row[4] ?? 0,
                    'quantity'    => $row[5] ?? 1,
                    'depot_asal'  => trim($row[6] ?? ''),
                    'kota_tujuan' => trim($row[7] ?? ''),
                ];
            }
        }

        if (empty($rawRows)) {
            return back()->with('error', 'File kosong atau format tidak sesuai.');
        }

        // Buat lookup name → id sekali saja
        $depotMap = City::where('is_depot', 1)->where('is_active', 1)->pluck('id', 'name')->toArray();
        $cityMap  = City::where('is_active', 1)->pluck('id', 'name')->toArray();

        $importErrors = [];
        $groups       = []; // key: "depotId_cityId" → ['depot_id', 'city_id', 'items']

        foreach ($rawRows as $i => $row) {
            $lineNo    = $i + 2; // baris Excel (baris 1 adalah header)
            $depotName = $row['depot_asal'];
            $cityName  = $row['kota_tujuan'];

            if (empty($depotName)) {
                $importErrors[] = "Baris {$lineNo}: Kolom G (Depot Asal) kosong.";
                continue;
            }
            if (empty($cityName)) {
                $importErrors[] = "Baris {$lineNo}: Kolom H (Kota Tujuan) kosong.";
                continue;
            }

            $depotId = $depotMap[$depotName] ?? null;
            if (!$depotId) {
                $importErrors[] = "Baris {$lineNo}: Depot '{$depotName}' tidak ditemukan di database.";
                continue;
            }

            $cityId = $cityMap[$cityName] ?? null;
            if (!$cityId) {
                $importErrors[] = "Baris {$lineNo}: Kota tujuan '{$cityName}' tidak ditemukan di database.";
                continue;
            }

            $key = "{$depotId}_{$cityId}";
            if (!isset($groups[$key])) {
                $groups[$key] = ['depot_id' => $depotId, 'city_id' => $cityId, 'items' => []];
            }
            $groups[$key]['items'][] = $row;
        }

        if (empty($groups)) {
            return back()
                ->with('error', 'Tidak ada baris valid yang dapat diimport.')
                ->with('import_errors', $importErrors);
        }

        $totalItems = 0;
        DB::transaction(function () use ($groups, &$totalItems) {
            foreach ($groups as $group) {
                // Satu DeliveryOrder per kombinasi depot-asal + kota-tujuan
                $order = DeliveryOrder::create([
                    'order_date'           => Carbon::today(),
                    'origin_depot_id'      => $group['depot_id'],
                    'destination_city_id'  => $group['city_id'],
                    'source'               => 'excel',
                    'status'               => 'pending',
                ]);

                foreach ($group['items'] as $row) {
                    Item::create([
                        'order_id'     => $order->id,
                        'name'         => $row['name'],
                        'weight_kg'    => $row['weight_kg'],
                        'length_cm'    => $row['length_cm'],
                        'width_cm'     => $row['width_cm'],
                        'height_cm'    => $row['height_cm'],
                        'quantity'     => $row['quantity'],
                        'status'       => 'menunggu',
                        'is_carryover' => false,
                    ]);
                    $totalItems++;
                }
            }
        });

        return redirect()->route('pso.orders')
            ->with('success', "{$totalItems} barang dari Excel berhasil diimport!")
            ->with('import_errors', $importErrors);
    }

    // Endpoint API untuk ambil data items
    public function items()
    {
        $items = Item::where('status', 'menunggu')
            ->where(function ($q) {
                $q->whereHas('deliveryOrder', fn($dq) =>
                    $dq->whereDate('order_date', Carbon::today())->where('status', 'pending')
                )
                ->orWhere(function ($cq) {
                    $cq->where('is_carryover', true)
                       ->whereHas('deliveryOrder', fn($dq) => $dq->where('status', 'pending'));
                });
            })
            ->with('deliveryOrder.originDepot', 'deliveryOrder.destinationCity')
            ->get()->map(fn($item) => [
                'id'            => $item->id,
                'nama'          => $item->name,
                'panjang'       => (float) $item->length_cm,
                'lebar'         => (float) $item->width_cm,
                'tinggi'        => (float) $item->height_cm,
                'berat_fisik'   => (float) $item->weight_kg,
                'kota_asal'     => $item->deliveryOrder->originDepot->name ?? '-',
                'kota_tujuan'   => $item->deliveryOrder->destinationCity->name ?? '-',
                'is_carryover'  => (bool) $item->is_carryover,
            ]);
        return response()->json($items);
    }

    // Halaman Optimasi & Hasil PSO
    public function results()
    {
        // Data semua kota — dikirim ke view supaya JS tahu mana depot dan mana kota biasa
        $allCities = City::where('is_active', true)
            ->get()
            ->map(fn($c) => [
                'name'     => $c->name,
                'lat'      => (float) $c->latitude,
                'lon'      => (float) $c->longitude,
                'is_depot' => (bool) $c->is_depot,
            ]);
    
        return view('pso.results', compact('allCities'));
    }

    // Jalankan Python PSO
    public function run(Request $request)
    {
        try {
            if (!function_exists('shell_exec')) {
                return response()->json(['error' => 'shell_exec tidak tersedia di PHP ini'], 500);
            }

            // Ambil semua barang yang perlu dikirim:
            // (1) barang baru hari ini dari order pending, ATAU
            // (2) barang carry-over dari order pending manapun (termasuk hari sebelumnya)
            $dbItems = Item::where('status', 'menunggu')
                ->where(function ($q) {
                    $q->whereHas('deliveryOrder', fn($dq) =>
                        $dq->whereDate('order_date', Carbon::today())->where('status', 'pending')
                    )
                    ->orWhere(function ($cq) {
                        $cq->where('is_carryover', true)
                           ->whereHas('deliveryOrder', fn($dq) => $dq->where('status', 'pending'));
                    });
                })
                ->with('deliveryOrder.originDepot', 'deliveryOrder.destinationCity')
                ->get();

            $rawItems = [];
            foreach ($dbItems as $item) {
                $rawItems[] = [
                    'id'            => $item->id,
                    'nama'          => $item->name,
                    'panjang'       => (float) $item->length_cm,
                    'lebar'         => (float) $item->width_cm,
                    'tinggi'        => (float) $item->height_cm,
                    'berat_fisik'   => (float) $item->weight_kg,
                    'kota_asal'     => $item->deliveryOrder->originDepot->name ?? 'Unknown',
                    'kota_tujuan'   => $item->deliveryOrder->destinationCity->name ?? 'Unknown',
                    'is_carryover'  => (bool) $item->is_carryover,
                ];
            }

            if (empty($rawItems)) {
                return response()->json(['error' => 'Tidak ada pesanan pending hari ini'], 400);
            }

            $trucksData = Truck::where('is_active', true)->with('homeDepot')->get()->map(fn($t) => [
                'id'            => $t->id,
                'plate_number'  => $t->plate_number,
                'max_weight_kg' => (float) $t->max_weight_kg,
                'box_p'         => (float) $t->length_cm,
                'box_l'         => (float) $t->width_cm,
                'box_t'         => (float) $t->height_cm,
                'depot_asal'    => $t->homeDepot->name ?? 'Unknown',
                'tarif_per_km'  => (float) ($t->tarif_per_km ?? 10000), // ← tambah ini
            ])->toArray();

            $cities  = City::where('is_active', true)->pluck('name')->toArray();
            $cityIdx = array_flip($cities);
            $coords  = City::where('is_active', true)->get()
                ->mapWithKeys(fn($c) => [$c->name => [(float)$c->latitude, (float)$c->longitude]])
                ->toArray();

            $n   = count($cities);
            $adj = array_fill(0, $n, array_fill(0, $n, 999999));  // angka besar, bukan INF
            for ($i = 0; $i < $n; $i++) $adj[$i][$i] = 0.0;

            foreach (DB::table('depot_distances')->get() as $d) {
                $cA = City::find($d->city_a_id)->name ?? null;
                $cB = City::find($d->city_b_id)->name ?? null;
                if ($cA && $cB && isset($cityIdx[$cA]) && isset($cityIdx[$cB])) {
                    $adj[$cityIdx[$cA]][$cityIdx[$cB]] = (float) $d->distance_km;
                }
            }

            $depotNames  = City::where('is_depot', 1)->pluck('name')->toArray();
            $psoSettings = Setting::where('param_group', 'pso')->pluck('param_value', 'param_key')->toArray();
            $opSettings  = Setting::where('param_group', 'operasional')->pluck('param_value', 'param_key')->toArray();

            $payload = json_encode([
                'items'      => $rawItems,
                'trucks'     => $trucksData,
                'graph'      => ['cities' => $cities, 'adj' => $adj, 'coords' => $coords, 'depot_names' => $depotNames],
                'pso_params' => $psoSettings,
                'op_params'  => $opSettings,
            ]);

            $enginePath = base_path('engine/run_pso.py');
            if (!file_exists($enginePath)) {
                return response()->json(['error' => "File Python tidak ditemukan: $enginePath"], 500);
            }

            $pythonPath = "D:\\Program\\Laragon\\bin\\python\\python-3.10\\python.exe";
            if (!file_exists($pythonPath)) {
                $pythonPath = "python";
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'pso_') . '.json';
            file_put_contents($tempFile, $payload);

            $projectPath = base_path();
            $command = "set \"PYTHONPATH={$projectPath}\" && \"{$pythonPath}\" \"{$enginePath}\" \"{$tempFile}\"";
            $output  = shell_exec($command . ' 2>&1');

            $result = json_decode($output, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error'         => 'Python tidak mengembalikan JSON valid',
                    'python_output' => $output,
                    'command'       => $command,
                ], 500);
            }

            // ── Filter truk "ghost" (idle, tidak bawa barang) dari output PSO ──
            // Ini mencegah PSO menampilkan lebih banyak truk dari yang aktif karena
            // partikel PSO bisa menghasilkan entri untuk truk yang tidak kebagian barang.
            if (isset($result['best_routes'])) {
                $result['best_routes'] = array_filter(
                    $result['best_routes'],
                    fn($route) => !empty($route['items'])
                );
            }

            session(['hasil_pso_temp' => $result]);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'PHP Exception: ' . $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], 500);
        }
    }

    // Simpan ke DB
    public function save(Request $request)
    {
        $hasil = session('hasil_pso_temp');
        if (!$hasil) return redirect()->back()->withErrors('Tidak ada hasil optimasi.');
    
        DB::beginTransaction();
        try {
            $today   = Carbon::today();
            $batchId = 'PSO-' . $today->format('Ymd') . '-' . time(); // ← tambah batch_id
    
            foreach ($hasil['best_routes'] as $truckId => $ri) {
                SimulationResult::create([
                    'batch_id'          => $batchId,           // ← tambah ini
                    'run_date'          => $today,
                    'truck_id'          => $truckId,
                    'route_json'        => ['rute' => $ri['rute'] ?? []],
                    'total_weight_kg'   => $ri['berat_muatan'] ?? 0,
                    'total_volume_m3'   => $ri['volume_m3'] ?? 0,  // ← tambah ini
                    'tariff_total'      => $ri['tarif'] ?? 0,
                    'fuel_cost'         => $ri['biaya_bbm'] ?? 0,
                    'net_profit'        => ($ri['tarif'] ?? 0) - ($ri['biaya_bbm'] ?? 0),
                    'gbest_curve_json'  => $hasil['gbest_curve'] ?? [],
                ]);
    
                foreach ($ri['items'] ?? [] as $it) {
                    Item::where('id', $it['id'])->update(['status' => 'terkirim']);
                }
            }
    
            // Ambil order_id dari barang yang tidak masuk truk manapun (akan jadi carry-over)
            $carryoverOrderIds = Item::where('status', 'menunggu')
                ->pluck('order_id')->unique()->values()->toArray();

            // Tandai sisa barang sebagai carry-over untuk besok
            Item::where('status', 'menunggu')->update(['is_carryover' => true]);

            // Tandai pesanan hari ini sebagai selesai, KECUALI yang masih
            // punya barang carry-over — biarkan 'pending' supaya terpick-up
            // oleh query run() besok tanpa perlu filter tanggal
            DeliveryOrder::whereDate('order_date', $today)
                ->whereNotIn('id', $carryoverOrderIds)
                ->update(['status' => 'selesai']);

            DB::commit();
            session()->forget('hasil_pso_temp');
            return redirect()->route('pso.results')->with('success', 'Hasil optimasi tersimpan!');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal simpan: ' . $e->getMessage());
        }
    }
}