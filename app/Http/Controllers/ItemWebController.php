<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemWebController extends Controller
{
    /**
     * Menampilkan Database Barang (Read + Filter)
     * TIDAK ADA CREATE karena barang hanya masuk dari Input Pengiriman
     */
    public function index(Request $request)
    {
        $query = DB::table('items')
            ->join('delivery_orders', 'items.order_id', '=', 'delivery_orders.id')
            ->leftJoin('cities as origin', 'delivery_orders.origin_depot_id', '=', 'origin.id')
            ->leftJoin('cities as dest', 'delivery_orders.destination_city_id', '=', 'dest.id')
            ->select(
                'items.id',
                'items.order_id',
                'items.name',
                'items.length_cm',
                'items.width_cm',
                'items.height_cm',
                'items.weight_kg',
                'items.status',
                'delivery_orders.order_date',
                'origin.name as depot_asal',
                'dest.name as kota_tujuan'
            );

        // ==========================================
        // FILTER LOGIC
        // ==========================================
        
        // 1. Filter Status
        if ($request->filled('filter_status')) {
            $query->where('items.status', $request->filter_status);
        }

        // 2. Filter Depot Asal
        if ($request->filled('filter_depot')) {
            $query->where('delivery_orders.origin_depot_id', $request->filter_depot);
        }

        // 3. Filter Tanggal
        if ($request->filled('filter_date')) {
            $query->whereDate('delivery_orders.order_date', $request->filter_date);
        }

        // Ambil data dengan pagination
        $items = $query->orderByDesc('delivery_orders.order_date')->paginate(15)->withQueryString();

        // Data untuk dropdown filter
        $depots = DB::table('cities')->where('is_depot', true)->where('is_active', true)->pluck('name', 'id');

        return view('items.index', compact('items', 'depots'));
    }

    /**
     * Menampilkan form edit detail item
     */
    public function edit($id)
    {
        // Ambil data item beserta info ordernya
        $item = DB::table('items')
            ->join('delivery_orders', 'items.order_id', '=', 'delivery_orders.id')
            ->leftJoin('cities as origin', 'delivery_orders.origin_depot_id', '=', 'origin.id')
            ->leftJoin('cities as dest', 'delivery_orders.destination_city_id', '=', 'dest.id')
            ->select(
                'items.*', 
                'delivery_orders.order_date',
                'origin.name as depot_asal',
                'dest.name as kota_tujuan'
            )
            ->where('items.id', $id)
            ->first();

        if (!$item) {
            abort(404, 'Item tidak ditemukan');
        }

        return view('items.edit', compact('item'));
    }

    /**
     * Update detail item (jika ada salah input)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'       => 'required|string|max:150',
            'length_cm'  => 'required|numeric|min:0.1',
            'width_cm'   => 'required|numeric|min:0.1',
            'height_cm'  => 'required|numeric|min:0.1',
            'weight_kg'  => 'required|numeric|min:0.1',
        ]);

        DB::table('items')->where('id', $id)->update([
            'name'       => $request->name,
            'length_cm'  => $request->length_cm,
            'width_cm'   => $request->width_cm,
            'height_cm'  => $request->height_cm,
            'weight_kg'  => $request->weight_kg,
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->route('items.index')->with('success', 'Detail barang berhasil diperbarui.');
    }
}
