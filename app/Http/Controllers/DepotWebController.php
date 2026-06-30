<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Truck;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DepotWebController extends Controller
{
    public function index()
    {
        $depots = City::where('is_depot', true)
            ->orderBy('is_depot', 'desc') // Depot aktif di atas
            ->orderBy('name', 'asc')
            ->get();

        $allTrucks = \App\Models\Truck::where('is_active', true)->get();
        $trucksByDepot = $allTrucks->groupBy('current_city_id');

        $totalTrucks = $allTrucks->count();
        $totalAvail   = $allTrucks->where('operational_status', 'available')->count();
        $totalDuty    = $allTrucks->where('operational_status', 'on_duty')->count();
        $totalMaint   = $allTrucks->where('operational_status', 'maintenance')->count();

        return view('depot.index', compact(
            'depots',
            'trucksByDepot',
            'totalTrucks',
            'totalAvail',
            'totalDuty',
            'totalMaint'
        ));
    }


    public function updateCapacity(Request $request)
    {
        $depot = \App\Models\City::findOrFail($request->depot_id);

        $depot->update($request->only(['max_truck_capacity', 'max_warehouse_kg', 'is_active']));

        return back()->with('success', 'Status gudang diperbarui.');
    }
}
