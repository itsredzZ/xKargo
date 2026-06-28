<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderWebController extends Controller
{
    /**
     * Menampilkan halaman Input Pengiriman
     */
    public function index()
    {
        // Query data yang dibutuhkan di halaman form (dropdown, dll)
        $depots = DB::table('cities')->where('is_depot', true)->where('is_active', true)->get();
        $cities = DB::table('cities')->where('is_active', true)->get();
        
        // Query carryover (nanti bisa ditambahkan)
        // $carryovers = DB::table('carryover_items')->where('resolved', false)->get();

        return view('pso.orders', compact('depots', 'cities'));
    }

    /**
     * Menyimpan pesanan baru ke database
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $validated = $request->validate([
            'name'                 => 'required|string|max:150',
            'length_cm'            => 'required|numeric|min:0.1',
            'width_cm'             => 'required|numeric|min:0.1',
            'height_cm'            => 'required|numeric|min:0.1',
            'weight_kg'            => 'required|numeric|min:0.1',
            'origin_depot_id'      => 'required|exists:cities,id',
            'destination_city_id'  => 'required|exists:cities,id',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Buat Header Delivery Order
            $orderId = DB::table('delivery_orders')->insertGetId([
                'order_date'           => Carbon::today(),
                'origin_depot_id'      => $validated['origin_depot_id'],
                'destination_city_id'  => $validated['destination_city_id'],
                'quantity'             => 1, // Default 1 paket dulu
                'source'               => 'manual',
                'created_at'           => Carbon::now(),
                'updated_at'           => Carbon::now(),
            ]);

            // 2. Buat Item Barang
            DB::table('items')->insert([
                'order_id'    => $orderId,
                'name'        => $validated['name'],
                'length_cm'   => $validated['length_cm'],
                'width_cm'    => $validated['width_cm'],
                'height_cm'   => $validated['height_cm'],
                'weight_kg'   => $validated['weight_kg'],
                'status'      => 'menunggu',
                'is_carryover'=> false,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ]);
        });

        // Redirect kembali ke halaman input dengan pesan sukses
        return redirect()->route('pso.orders')->with('success', 'Pesanan berhasil ditambahkan ke database!');
    }
}
