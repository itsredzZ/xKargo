<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\DepotDistance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    /**
     * GET /api/cities
     * Semua kota aktif — untuk dropdown tujuan.
     */
    public function index(): JsonResponse
    {
        $cities = City::active()->orderBy('name')->get();

        return response()->json([
            'data' => $cities->map(fn($c) => [
                'id'                 => $c->id,
                'name'               => $c->name,
                'latitude'           => $c->latitude,
                'longitude'          => $c->longitude,
                'is_depot'           => $c->is_depot,
                'is_active'          => $c->is_active,
                'max_truck_capacity' => $c->max_truck_capacity,
                'max_warehouse_kg'   => $c->max_warehouse_kg,
            ]),
        ]);
    }

    /**
     * GET /api/cities/depots
     * Hanya depot aktif — dipanggil untuk halaman peta.
     */
    public function depots(): JsonResponse
    {
        $depots = City::depot()->orderBy('name')->get();

        return response()->json([
            'data' => $depots->map(fn($d) => [
                'id'                 => $d->id,
                'name'               => $d->name,
                'lat'                => $d->latitude,
                'lon'                => $d->longitude,
                'max_truck_capacity' => $d->max_truck_capacity,
                'max_warehouse_kg'   => $d->max_warehouse_kg,
            ]),
        ]);
    }

    /**
     * GET /api/cities/matrix
     * Matriks jarak lengkap untuk arsitektur pencarian rute (PSO Engine).
     */
    public function matrix(): JsonResponse
    {
        // 1. Ambil semua kota yang AKTIF saja
        $allCities = City::active()->orderBy('name')->get();
        $activeCityIds = $allCities->pluck('id')->toArray();

        // 2. OPTIMASI DATABASE BARU: Hanya ambil jarak di mana KEDUA kota tersebut aktif
        $distances = DepotDistance::whereIn('city_a_id', $activeCityIds)
                                  ->whereIn('city_b_id', $activeCityIds)
                                  ->get();

        $cities  = $allCities->pluck('name')->toArray();
        $n       = count($cities);
        $cityIdx = array_flip($cities);
        $idToName = $allCities->pluck('name', 'id')->toArray();

        // Koordinat
        $coords = $allCities->mapWithKeys(fn($c) => [
            $c->name => [(float)$c->latitude, (float)$c->longitude]
        ])->toArray();

        // Adj matrix — inisialisasi dengan INF (null)
        $adj = array_fill(0, $n, array_fill(0, $n, null)); 
        for ($i = 0; $i < $n; $i++) {
            $adj[$i][$i] = 0.0;
        }

        // Mapping jarak ke matriks dua arah
        foreach ($distances as $d) {
            $a = $idToName[$d->city_a_id] ?? null;
            $b = $idToName[$d->city_b_id] ?? null;
            
            if ($a && isset($cityIdx[$a]) && $b && isset($cityIdx[$b])) {
                $i = $cityIdx[$a];
                $j = $cityIdx[$b];
                
                // Float casting otomatis berkat model baru kita
                $adj[$i][$j] = $d->distance_km;
                $adj[$j][$i] = $d->distance_km;
            }
        }

        return response()->json([
            'cities'   => $cities,
            'city_idx' => $cityIdx,
            'adj'      => $adj,       
            'coords'   => $coords,
        ]);
    }

    /**
     * POST /api/cities
     * Tambah kota baru. Database baru akan otomatis mengisi created_at & updated_at.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:100|unique:cities',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'is_depot'  => 'boolean',
            'is_active' => 'boolean',
        ]);

        $city = City::create([
            ...$validated,
            'is_depot'  => $validated['is_depot'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => "Kota '{$city->name}' berhasil ditambahkan.",
            'data'    => ['id' => $city->id, 'name' => $city->name],
        ], 201);
    }

    /**
     * PATCH /api/cities/{id}
     * Update data kota. updated_at akan otomatis terisi oleh Laravel.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $city = City::findOrFail($id);

        $validated = $request->validate([
            'is_depot'           => 'sometimes|boolean',
            'is_active'          => 'sometimes|boolean',
            'max_truck_capacity' => 'sometimes|nullable|integer|min:1',
            'max_warehouse_kg'   => 'sometimes|nullable|numeric|min:1000',
        ]);

        $city->update($validated);

        return response()->json(['message' => "Kota '{$city->name}' diperbarui."]);
    }
}