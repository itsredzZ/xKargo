<?php
// app/Http/Controllers/DepotWebController.php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Truck;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DepotWebController extends Controller
{
    /**
     * Menampilkan halaman dasbor atau ringkasan status operasional depot dan armada truk.
     * * Mengambil data semua depot serta mengelompokkan data seluruh truk yang aktif 
     * berdasarkan lokasinya saat ini untuk kalkulasi statistik operasional (KPI Cards).
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // 1. Ambil semua data kota yang dikategorikan sebagai Depot Utama, diurutkan berdasarkan abjad (A-Z)
        $depots = City::where('is_depot', true)
            ->orderBy('is_depot', 'desc') // *Catatan: orderBy ini redundan karena klausul 'where' sudah menyaring hanya yang true
            ->orderBy('name', 'asc')
            ->get();

        // 2. Ambil seluruh armada truk yang aktif dari database dalam satu kali query (Eager Loading Optimization)
        $allTrucks = \App\Models\Truck::where('is_active', true)->get();
        
        // 3. Kelompokkan objek truk berdasarkan lokasi kota saat ini ('current_city_id') menggunakan Laravel Collection
        //    Langkah ini menghindari query berulang ke database (N+1 query issue) saat rendering di dalam view
        $trucksByDepot = $allTrucks->groupBy('current_city_id');

        // 4. Hitung ringkasan statistik (KPI Metrics) dari total armada untuk ditampilkan pada komponen Kartu Informasi (Cards)
        $totalTrucks = $allTrucks->count();
        $totalAvail   = $allTrucks->where('operational_status', 'available')->count();
        $totalDuty    = $allTrucks->where('operational_status', 'on_duty')->count();
        $totalMaint   = $allTrucks->where('operational_status', 'maintenance')->count();

        // 5. Kembalikan ke antarmuka (view) beserta seluruh variabel matriks yang dibutuhkan dashboard
        return view('depot.index', compact(
            'depots',
            'trucksByDepot',
            'totalTrucks',
            'totalAvail',
            'totalDuty',
            'totalMaint'
        ));
    }

    /**
     * Memperbarui parameter kapasitas logistik dan status operasional dari suatu depot.
     * * Fitur ini mengizinkan pembaruan batas maksimal armada truk, kapasitas tampung gudang,
     * serta status keaktifan depot secara langsung melalui request terpadu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCapacity(Request $request)
    {
        // 1. Ambil entitas data kota berdasarkan ID Depot yang dikirim, atau lemparkan error 404 jika tidak ditemukan
        $depot = \App\Models\City::findOrFail($request->depot_id);

        // 2. Eksekusi pembaruan data secara selektif (Mass Assignment Protection)
        //    *Catatan: Menggunakan 'only' menjamin parameter asing/berbahaya di luar 3 kolom ini akan diabaikan oleh sistem
        $depot->update($request->only(['max_truck_capacity', 'max_warehouse_kg', 'is_active']));

        // 3. Alihkan pengguna kembali ke halaman dashboard depot dengan membawa flash alert sukses
        return back()->with('success', 'Status gudang diperbarui.');
    }
}
