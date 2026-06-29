<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TruckController extends Controller
{
    // Semua truk aktif 
    public function index(Request $request): JsonResponse
    {
        $query = Truck::with(['homeDepot', 'currentCity'])
                      ->where('is_active', true);

        if ($request->has('status')) {
            $query->where('operational_status', $request->status);
        }

        if ($request->has('depot_id')) {
            $query->where('home_depot_id', $request->depot_id);
        }

        $trucks = $query->get();

        return response()->json([
            'data' => $trucks->map(fn($t) => $this->formatTruck($t)),
        ]);
    }

    // Hanya truk available
    public function available(): JsonResponse
    {
        $trucks = Truck::with(['homeDepot', 'currentCity'])
                       ->available()
                       ->get();

        return response()->json([
            'data' => $trucks->map(fn($t) => $this->formatTruck($t)),
        ]);
    }

    // Detail satu truk
    public function show(int $id): JsonResponse
    {
        $truck = Truck::with(['homeDepot', 'currentCity'])->findOrFail($id);
        return response()->json(['data' => $this->formatTruck($truck)]);
    }

    // Tambah truk baru
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plate_number'                 => 'required|string|max:20|unique:trucks',
            'max_weight_kg'                => 'required|numeric|min:100',
            'length_cm'                    => 'required|numeric|min:50',
            'width_cm'                     => 'required|numeric|min:50',
            'height_cm'                    => 'required|numeric|min:50',
            'home_depot_id'                => 'required|integer|exists:cities,id',
            'truck_type'                   => 'required|in:mobil_box,pickup,cde,cdd,cdd_long,fuso,tronton,prime_mover',
            'fuel_efficiency_km_per_liter' => 'required|numeric|min:1|max:30',
            'operational_status'           => 'sometimes|in:available,on_duty,maintenance',
        ]);

        $truck = Truck::create([
            ...$validated,
            'current_city_id'    => $validated['home_depot_id'],
            'operational_status' => $validated['operational_status'] ?? 'available',
            'is_active'          => true,
        ]);

        return response()->json([
            'message' => "Truk '{$truck->plate_number}' berhasil ditambahkan.",
            'data'    => $this->formatTruck($truck->load(['homeDepot', 'currentCity'])),
        ], 201);
    }

    // Update truk
    public function update(Request $request, int $id): JsonResponse
    {
        $truck = Truck::findOrFail($id);

        $validated = $request->validate([
            'plate_number'                 => "sometimes|string|max:20|unique:trucks,plate_number,{$id}",
            'max_weight_kg'                => 'sometimes|numeric|min:100',
            'length_cm'                    => 'sometimes|numeric|min:50',
            'width_cm'                     => 'sometimes|numeric|min:50',
            'height_cm'                    => 'sometimes|numeric|min:50',
            'operational_status'           => 'sometimes|in:available,on_duty,maintenance',
            'truck_type'                   => 'sometimes|in:mobil_box,pickup,cde,cdd,cdd_long,fuso,tronton,prime_mover',
            'fuel_efficiency_km_per_liter' => 'sometimes|numeric|min:1|max:30',
            'current_city_id'              => 'sometimes|integer|exists:cities,id',
        ]);

        $truck->update($validated);

        return response()->json([
            'message' => "Truk '{$truck->plate_number}' diperbarui.",
            'data'    => $this->formatTruck($truck->fresh(['homeDepot', 'currentCity'])),
        ]);
    }

    // Update hanya status operasional
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $truck = Truck::findOrFail($id);

        $validated = $request->validate([
            'status'          => 'required|in:available,on_duty,maintenance',
            'current_city_id' => 'sometimes|integer|exists:cities,id',
        ]);

        $truck->operational_status = $validated['status'];
        if (isset($validated['current_city_id'])) {
            $truck->current_city_id = $validated['current_city_id'];
        }
        $truck->save();

        return response()->json([
            'message' => "Status truk '{$truck->plate_number}' → {$validated['status']}.",
        ]);
    }

    private function formatTruck(Truck $t): array
    {
        return [
            'truck_id'                     => $t->id,
            'plate'                        => $t->plate_number,
            'truck_type'                   => $t->truck_type,
            'truck_type_label'             => $t->truck_type_label,
            'operational_status'           => $t->operational_status,
            'depot'                        => $t->homeDepot?->name ?? '?',
            'current_city'                 => $t->currentCity?->name ?? $t->homeDepot?->name ?? '?',
            'max_weight_kg'                => $t->max_weight_kg,
            'length_cm'                    => $t->length_cm,
            'width_cm'                     => $t->width_cm,
            'height_cm'                    => $t->height_cm,
            'volume_cm3'                   => round($t->length_cm * $t->width_cm * $t->height_cm),
            'volume_m3'                    => $t->volumem3,
            'fuel_efficiency_km_per_liter' => $t->fuel_efficiency_km_per_liter,
        ];
    }
}
