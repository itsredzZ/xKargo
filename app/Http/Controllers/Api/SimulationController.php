<?php
// app/Http/Controllers/Api/SimulationController.php
// PSO engine Streamlit POST hasil run ke sini setelah selesai.
// Laravel menyimpan ke simulation_results dan bisa tampilkan di dashboard.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SimulationResult;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SimulationController extends Controller
{
    /**
     * GET /api/simulations
     * Riwayat hasil PSO — untuk dashboard Laravel.
     * ?date=2025-01-15  → filter per tanggal
     * ?truck_id=3       → filter per truk
     */
    public function index(Request $request): JsonResponse
    {
        $query = SimulationResult::with('truck.homeDepot')
                                 ->orderByDesc('run_date')
                                 ->orderByDesc('id');

        if ($request->has('date')) {
            $query->whereDate('run_date', $request->date);
        }
        if ($request->has('truck_id')) {
            $query->where('truck_id', $request->truck_id);
        }

        $results = $query->limit(100)->get();

        return response()->json([
            'data' => $results->map(fn($r) => [
                'id'              => $r->id,
                'run_date'        => $r->run_date->toDateString(),
                'truck_id'        => $r->truck_id,
                'truck_plate'     => $r->truck?->plate_number,
                'depot'           => $r->truck?->homeDepot?->name,
                'route_json'      => $r->route_json,
                'total_weight_kg' => $r->total_weight_kg,
                'total_volume_m3' => $r->total_volume_m3,
                'tariff_total'    => $r->tariff_total,
                'fuel_cost'       => $r->fuel_cost,
                'net_profit'      => $r->net_profit,
                'gbest_curve'     => $r->gbest_curve_json,
            ]),
        ]);
    }

    /**
     * POST /api/simulations
     * PSO engine Streamlit kirim hasil run ke sini.
     *
     * Contoh body dari Streamlit:
     * {
     *   "run_date": "2025-01-15",
     *   "results": [
     *     {
     *       "truck_id": 1,
     *       "route_json": {"stops": ["Surabaya","Malang","Surabaya"]},
     *       "total_weight_kg": 850.0,
     *       "total_volume_m3": 1.23,
     *       "tariff_total": 120000,
     *       "fuel_cost": 45000,
     *       "net_profit": 75000,
     *       "gbest_curve_json": [0.9, 0.85, ...]
     *     }
     *   ]
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'run_date'              => 'required|date',
            'results'               => 'required|array|min:1',
            'results.*.truck_id'    => 'required|integer|exists:trucks,id',
            'results.*.route_json'  => 'nullable|array',
            'results.*.total_weight_kg'  => 'required|numeric|min:0',
            'results.*.total_volume_m3'  => 'required|numeric|min:0',
            'results.*.tariff_total'     => 'required|numeric|min:0',
            'results.*.fuel_cost'        => 'required|numeric|min:0',
            'results.*.net_profit'       => 'required|numeric',
            'results.*.gbest_curve_json' => 'nullable|array',
        ]);

        $saved = 0;
        foreach ($validated['results'] as $r) {
            SimulationResult::create([
                'run_date'         => $validated['run_date'],
                'truck_id'         => $r['truck_id'],
                'route_json'       => $r['route_json'] ?? null,
                'total_weight_kg'  => $r['total_weight_kg'],
                'total_volume_m3'  => $r['total_volume_m3'],
                'tariff_total'     => $r['tariff_total'],
                'fuel_cost'        => $r['fuel_cost'],
                'net_profit'       => $r['net_profit'],
                'gbest_curve_json' => $r['gbest_curve_json'] ?? null,
            ]);
            $saved++;
        }

        return response()->json([
            'message' => "{$saved} hasil simulasi PSO berhasil disimpan.",
            'run_date' => $validated['run_date'],
        ], 201);
    }

    /**
     * GET /api/simulations/settings
     * PSO engine ambil parameter dari DB sebelum run.
     * Setara membaca tabel settings langsung dari Streamlit.
     */
    public function settings(): JsonResponse
    {
        return response()->json([
            'pso'          => Setting::getPsoParams(),
            'operasional'  => Setting::getOperasionalParams(),
        ]);
    }

    /**
     * PUT /api/simulations/settings
     * Admin update parameter PSO dari dashboard Laravel.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings'               => 'required|array',
            'settings.*.param_group' => 'required|in:pso,operasional',
            'settings.*.param_key'   => 'required|string|max:50',
            'settings.*.param_value' => 'required|string|max:50',
        ]);

        $updated = 0;
        foreach ($validated['settings'] as $s) {
            Setting::updateOrCreate(
                ['param_group' => $s['param_group'], 'param_key' => $s['param_key']],
                ['param_value' => $s['param_value']]
            );
            $updated++;
        }

        return response()->json(['message' => "{$updated} parameter diperbarui."]);
    }
}
