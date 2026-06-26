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
    // Preset sinkron dengan TRUCK_PRESETS di 2_Master_Data_Truk.py
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

    public function index(\Illuminate\Http\Request $request)
    {
        // 1. Siapkan kerangka pencarian
        $query = Truck::with(['homeDepot', 'currentCity'])->where('is_active', true);

        // 2. Tangkap perintah Filter Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('operational_status', $request->status);
        }

        // 3. Tangkap perintah Pencarian Teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                    ->orWhereHas('homeDepot', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // 4. Eksekusi dengan Paginasi (withQueryString agar filter tidak hilang saat pindah halaman)
        $trucks = $query->orderBy('home_depot_id')
            ->orderBy('plate_number')
            ->paginate(10)
            ->withQueryString();

        // Data utuh untuk Kartu KPI di atas (agar angkanya tetap 12 walau difilter)
        $allTrucks = Truck::where('is_active', true)->get();

        $depots  = City::depot()->orderBy('name')->get();
        $presets = self::PRESETS;
        $breadcrumb = ['Armada Truk' => null];

        $summary = [
            'total'       => $allTrucks->count(),
            'available'   => $allTrucks->where('operational_status', 'available')->count(),
            'on_duty'     => $allTrucks->where('operational_status', 'on_duty')->count(),
            'maintenance' => $allTrucks->where('operational_status', 'maintenance')->count(),
        ];

        return view('trucks.index', compact('trucks', 'allTrucks', 'depots', 'presets', 'summary', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        $aturan = [
            'plate_number'                 => 'required|string|max:20|unique:trucks',
            'truck_type'                   => 'required|in:' . implode(',', array_keys(self::PRESETS)),
            'max_weight_kg'                => 'required|numeric|min:100',
            'length_cm'                    => 'required|numeric|min:50',
            'width_cm'                     => 'required|numeric|min:50',
            'height_cm'                    => 'required|numeric|min:50',
            'fuel_efficiency_km_per_liter' => 'required|numeric|min:1|max:30',
            'home_depot_id'                => 'required|integer|exists:cities,id',
        ];

        $pesanKhusus = [
            'plate_number.unique'   => 'Nomor kendaraan ini sudah terdaftar. Silakan periksa kembali.',
            'plate_number.required' => 'Nomor kendaraan wajib diisi.',
        ];

        $validated = $request->validate($aturan, $pesanKhusus);

        Truck::create([
            ...$validated,
            'current_city_id'    => $validated['home_depot_id'],
            'operational_status' => 'available',
            'is_active'          => true,
        ]);

        return back()->with('success', "Kendaraan dengan plat '{$validated['plate_number']}' berhasil ditambahkan.");
    }

    public function update(Request $request, int $id)
    {
        $truck = Truck::findOrFail($id);

        $aturan = [
            'plate_number'                 => "required|string|max:20|unique:trucks,plate_number,{$id}",
            'truck_type'                   => 'required|in:' . implode(',', array_keys(self::PRESETS)),
            'operational_status'           => 'required|in:available,on_duty,maintenance',
            'max_weight_kg'                => 'required|numeric|min:100',
            'length_cm'                    => 'required|numeric|min:50',
            'width_cm'                     => 'required|numeric|min:50',
            'height_cm'                    => 'required|numeric|min:50',
            'fuel_efficiency_km_per_liter' => 'required|numeric|min:1|max:30',
        ];

        $pesanKhusus = [
            'plate_number.unique'   => 'Nomor kendaraan ini sudah digunakan oleh unit lain di sistem.',
            'plate_number.required' => 'Nomor kendaraan wajib diisi.',
        ];

        $validated = $request->validate($aturan, $pesanKhusus);

        $truck->update($validated);

        return back()->with('success', "Informasi kendaraan '{$truck->plate_number}' berhasil diperbarui.");
    }

    // Tambahkan method ini di TruckWebController
    public function getPresets()
    {
        return response()->json(self::PRESETS);
    }

    public function destroy(int $id)
    {
        $truck = Truck::findOrFail($id);
        $truck->update(['is_active' => false]); // soft delete
        return back()->with('success', "Truk dengan plat '{$truck->plate_number}' dinonaktifkan.");
    }
}
