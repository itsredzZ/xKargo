<?php
// app/Http/Controllers/TruckWebController.php
// Pola 1: Baca/tulis langsung ke db_xkargo.trucks
// Setara halaman 2_Master_Data_Truk.py di Streamlit

namespace App\Http\Controllers;

use App\Models\Truck;
use App\Models\City;
use Illuminate\Http\Request;

class TruckWebController extends Controller
{
    /**
     * Konfigurasi Standar (Presets) Jenis Armada Truk Logistik.
     * * Menyimpan spesifikasi teknis bawaan untuk setiap tipe kendaraan operasional XKargo.
     * Atribut ini krusial digunakan sebagai blueprint validasi kapasitas muatan (kubikasi & berat) 
     * serta kalkulasi efisiensi biaya bahan bakar pada algoritma optimasi rute.
     *
     * Struktur Array Detail:
     * - label  : Nama visual kendaraan untuk antarmuka pengguna (UI).
     * - max_kg : Batas muatan berat maksimal dalam satuan Kilogram (Kg).
     * - p      : Panjang karoseri box dalam satuan Centimeter (Cm).
     * - l      : Lebar karoseri box dalam satuan Centimeter (Cm).
     * - t      : Tinggi karoseri box dalam satuan Centimeter (Cm).
     * - fuel   : Konsumsi BBM (Rasio efisiensi jarak tempuh, satuan: Kilometer per Liter / Km/L).
     */
    const PRESETS = [
        'mobil_box'   => ['label' => 'Mobil Box',    'max_kg' => 1000,  'p' => 200,  'l' => 130, 't' => 130, 'fuel' => 12.0],
        'pickup'      => ['label' => 'Pickup',        'max_kg' => 2000,  'p' => 250,  'l' => 160, 't' => 130, 'fuel' => 10.0],
        'cde'         => ['label' => 'CDE (Engkel)',  'max_kg' => 3000,  'p' => 310,  'l' => 170, 't' => 170, 'fuel' => 8.0],
        'cdd'         => ['label' => 'CDD (6 Roda)', 'max_kg' => 8000,  'p' => 430,  'l' => 200, 't' => 200, 'fuel' => 6.5],
        'cdd_long'    => ['label' => 'CDD Long',      'max_kg' => 6000,  'p' => 530,  'l' => 200, 't' => 200, 'fuel' => 6.0],
        'fuso'        => ['label' => 'Fuso Box',      'max_kg' => 8000,  'p' => 600,  'l' => 240, 't' => 240, 'fuel' => 5.0],
        'tronton'     => ['label' => 'Tronton',       'max_kg' => 20000, 'p' => 950,  'l' => 240, 't' => 240, 'fuel' => 3.5],
        'prime_mover' => ['label' => 'Trailer 40ft',  'max_kg' => 30000, 'p' => 1200, 'l' => 240, 't' => 240, 'fuel' => 2.5],
    ];

    /**
     * Menampilkan daftar armada truk aktif dengan fitur pencarian, filter status, dan statistik.
     * * Menggunakan teknik Dynamic Query Building untuk menyaring data berdasarkan input user,
     * serta mempertahankan parameter filter pada link halaman berikutnya (Pagination appending).
     * Tetap menghitung statistik total armada secara global terlepas dari filter yang aktif.
     *
     * @param  \Illuminate\Http\Request  $request  Objek HTTP request yang membawa parameter filter/search.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(\Illuminate\Http\Request $request)
    {
        // 1. Inisialisasi awal Query Builder dan muat relasi depot asal serta posisi kota saat ini (Eager Loading)
        $query = Truck::with(['homeDepot', 'currentCity'])->where('is_active', true);

        // 2. FILTER STATUS OPERASIONAL: Jika parameter 'status' diisi dan nilainya bukan 'all' (misal: available, on_duty)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('operational_status', $request->status);
        }

        // 3. PENCARIAN TEKS DINAMIS: Menyaring berdasarkan nomor plat truk ATAU nama kota depot asalnya
        if ($request->filled('search')) {
            $search = $request->search;
            // Menggunakan fungsi penutup (closure) untuk mengelompokkan kondisi WHERE (SQL Parameter Grouping)
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                    ->orWhereHas('homeDepot', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%"); // Mencari teks pada tabel relasi 'cities'
                    });
            });
        }

        // 4. EKSEKUSI DATA BERHALAMAN: Urutkan data berdasarkan depot lalu nomor plat, batasi 10 baris per halaman.
        //    *withQueryString()* memastikan keyword pencarian/filter tidak hilang saat user mengklik halaman 2, 3, dst.
        $trucks = $query->orderBy('home_depot_id')
            ->orderBy('plate_number')
            ->paginate(10)
            ->withQueryString();

        // 5. STATISTIK GLOBAL (KPI): Mengambil semua truk aktif tanpa terpengaruh filter query di atas
        //    Langkah ini menjamin angka ringkasan di atas tabel tetap akurat (menampilkan total aset nyata)
        $allTrucks = Truck::where('is_active', true)->get();

        // 6. Sinkronisasi data master pendukung untuk kebutuhan komponen dropdown modal / form filter
        $depots      = City::depot()->orderBy('name')->get(); // Menggunakan local scope 'depot' pada model City
        $presets     = self::PRESETS;                         // Menarik konstanta spesifikasi jenis truk
        $breadcrumb  = ['Armada Truk' => null];

        // 7. Kalkulasi metrik ringkasan status operasional armada berbasis data memori (Collection)
        $summary = [
            'total'       => $allTrucks->count(),
            'available'   => $allTrucks->where('operational_status', 'available')->count(),
            'on_duty'     => $allTrucks->where('operational_status', 'on_duty')->count(),
            'maintenance' => $allTrucks->where('operational_status', 'maintenance')->count(),
        ];

        // 8. Kirim seluruh bundel data ke halaman interface view armada truk
        return view('trucks.index', compact('trucks', 'allTrucks', 'depots', 'presets', 'summary', 'breadcrumb'));
    }

    /**
     * Menyimpan data armada truk baru ke dalam database.
     * * Melakukan validasi ketat terhadap spesifikasi teknis kendaraan (dimensi, kapasitas, dan efisiensi bbm).
     * Secara otomatis menyinkronkan posisi awal truk ('current_city_id') agar berada di depot asalnya ('home_depot_id') 
     * serta mengeset status operasional awal menjadi 'available' (siap jalan).
     *
     * @param  \Illuminate\Http\Request  $request  Objek HTTP request yang membawa data form truk baru.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Definisikan aturan validasi (Validation Rules) untuk memastikan integritas data fisik truk
        $aturan = [
            'plate_number'                 => 'required|string|max:20|unique:trucks',
            // Memastikan jenis truk yang diinput wajib ada di dalam daftar kunci (keys) konstanta PRESETS
            'truck_type'                   => 'required|in:' . implode(',', array_keys(self::PRESETS)),
            'max_weight_kg'                => 'required|numeric|min:100',
            'length_cm'                    => 'required|numeric|min:50',
            'width_cm'                     => 'required|numeric|min:50',
            'height_cm'                    => 'required|numeric|min:50',
            'fuel_efficiency_km_per_liter' => 'required|numeric|min:1|max:30',
            // Memastikan ID Depot asal wajib valid dan terdaftar di tabel 'cities'
            'home_depot_id'                => 'required|integer|exists:cities,id',
        ];

        // 2. Kustomisasi pesan error (Custom Error Messages) untuk UX yang lebih ramah pengguna berbahasa Indonesia
        $pesanKhusus = [
            'plate_number.unique'   => 'Nomor kendaraan ini sudah terdaftar. Silakan periksa kembali.',
            'plate_number.required' => 'Nomor kendaraan wajib diisi.',
        ];

        // 3. Eksekusi validasi; Jika gagal, otomatis memicu redirect balik ke form dengan membawa pesan error
        $validated = $request->validate($aturan, $pesanKhusus);

        // 4. Buat record entitas truk baru di database menggunakan metode Mass Assignment
        //    Trik sintaks '...' (Array Spread Operator) digunakan untuk mengurai semua field yang lolos validasi
        Truck::create([
            ...$validated,
            'current_city_id'    => $validated['home_depot_id'], // Lokasi saat ini diatur sama dengan depot asal saat inisialisasi
            'operational_status' => 'available',                 // Status default armada siap beroperasi
            'is_active'          => true,                        // Menandakan truk aktif di dalam sistem logistik
        ]);

        // 5. Kembalikan pengguna ke halaman form sebelumnya dengan menyertakan pesan sukses via flash session
        return back()->with('success', "Kendaraan dengan plat '{$validated['plate_number']}' berhasil ditambahkan.");
    }

    /**
     * Memperbarui informasi dan spesifikasi teknis armada truk yang sudah ada.
     * * Melakukan validasi data pembaruan dengan menerapkan aturan pengecualian (exception) 
     * pada nomor plat kendaraan agar truk yang sedang diedit tidak menabrak aturan uniknya sendiri.
     *
     * @param  \Illuminate\Http\Request  $request  Objek HTTP request yang membawa data form pembaruan.
     * @param  int                        $id       ID unik truk yang akan diperbarui.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        // 1. Cari data entitas truk berdasarkan ID di database, atau lemparkan error 404 jika tidak ditemukan
        $truck = Truck::findOrFail($id);

        // 2. Susun aturan validasi pembaruan data
        $aturan = [
            // *PENTING: Menambahkan parameter ',$id' di akhir aturan unique agar database mengabaikan ID truk ini saat pengecekan duplikasi
            'plate_number'                 => "required|string|max:20|unique:trucks,plate_number,{$id}",
            'truck_type'                   => 'required|in:' . implode(',', array_keys(self::PRESETS)),
            'operational_status'           => 'required|in:available,on_duty,maintenance',
            'max_weight_kg'                => 'required|numeric|min:100',
            'length_cm'                    => 'required|numeric|min:50',
            'width_cm'                     => 'required|numeric|min:50',
            'height_cm'                    => 'required|numeric|min:50',
            'fuel_efficiency_km_per_liter' => 'required|numeric|min:1|max:30',
        ];

        // 3. Kustomisasi pesan error visual untuk memberikan kejelasan interaksi (UX) pada user interface
        $pesanKhusus = [
            'plate_number.unique'   => 'Nomor kendaraan ini sudah digunakan oleh unit lain di sistem.',
            'plate_number.required' => 'Nomor kendaraan wajib diisi.',
        ];

        // 4. Eksekusi validasi data input form pembaruan
        $validated = $request->validate($aturan, $pesanKhusus);

        // 5. Perbarui record data truk di database dengan kumpulan data yang telah divalidasi aman
        $truck->update($validated);

        // 6. Kembalikan pengguna ke antarmuka sebelumnya dengan membawa pesan notifikasi flash sukses
        return back()->with('success', "Informasi kendaraan '{$truck->plate_number}' berhasil diperbarui.");
    }

    /**
     * Menyediakan data standar (presets) spesifikasi truk dalam format JSON.
     * * Endpoint ini digunakan oleh sisi client (JavaScript/AJAX) pada halaman form
     * untuk mengisi kolom dimensi dan kapasitas secara otomatis saat user memilih jenis truk.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPresets()
    {
        // 1. Kembalikan konstanta PRESETS sebagai respon JSON dengan status HTTP 200 OK
        return response()->json(self::PRESETS);
    }

    /**
     * Menonaktifkan data armada truk dari sistem (Soft Delete Mandiri).
     * * Untuk menjaga integritas riwayat logistik pada data transaksi lama (simulasi/kargo),
     * truk tidak dihapus permanen dari database melainkan diubah status keaktifannya menjadi 'false'.
     *
     * @param  int  $id  ID unik truk yang akan dinonaktifkan.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        // 1. Cari data entitas truk berdasarkan ID, lemparkan error 404 jika tidak ditemukan
        $truck = Truck::findOrFail($id);

        // 2. Lakukan pengubahan status keaktifan (is_active = false) sebagai pengganti hapus permanen
        $truck->update(['is_active' => false]);

        // 3. Alihkan pengguna kembali ke halaman armada dengan membawa flash session berisi notifikasi sukses
        return back()->with('success', "Truk dengan plat '{$truck->plate_number}' dinonaktifkan.");
    }
}
