<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\DepotDistance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class CityWebController extends Controller
{
    // Menampilkan daftar kota
    public function index()
    {
        // 1. Ambil data kota secara alfabetis (A-Z) dengan batas 10 baris per halaman (untuk tabel)
        $cities = City::orderBy('name', 'asc')->paginate(10);

        // 2. Ambil seluruh data kota tanpa batasan (untuk merender titik lokasi di OpenStreetMap)
        $allCities = City::all();

        // 3. Kembalikan ke antarmuka (view) beserta kedua set data tersebut
        return view('cities.index', compact('cities', 'allCities'));
    }

    // Simpan kota baru
    public function store(Request $request)
    {
        // 1. Validasi input: Nama wajib diisi, maksimal 100 karakter, dan tidak boleh kembar (unik)
        $request->validate([
            'name'     => ['required', 'string', 'max:100', 'unique:cities,name'],
            'is_depot' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama kota wajib diisi.',
            'name.unique'   => 'Kota ini sudah terdaftar.',
        ]);

        // 2. Lakukan Geocoding otomatis untuk mendapatkan nilai Latitude dan Longitude berdasarkan nama kota
        [$lat, $lon] = $this->geocodeCity($request->name);

        // 3. Simpan data entitas kota baru ke dalam database
        $city = City::create([
            'name'      => trim($request->name),
            'latitude'  => $lat,
            'longitude' => $lon,
            'is_depot'  => $request->boolean('is_depot'),
            'is_active' => true,
        ]);

        // 4. Sinkronisasi jarak: Hitung rute baru dari kota ini ke semua kota lama via OSRM API
        $this->fillDistancesFromOsrm($city);

        // 5. Alihkan kembali ke halaman indeks dengan membawa pesan sukses (flash session)
        return redirect()->route('cities.index')
            ->with('success', "Kota \"{$city->name}\" berhasil ditambahkan.");
    }

    // Ubah status depot
    public function toggleDepot(City $city)
    {
        // 1. Balikkan nilai status depot saat ini (jika true menjadi false, jika false menjadi true)
        $city->update(['is_depot' => !$city->is_depot]);

        // 2. Tentukan label teks tipe kota yang baru untuk keperluan visualisasi notifikasi
        $tipe = $city->is_depot ? 'Depot' : 'Kota Reguler';

        // 3. Alihkan pengguna kembali ke halaman sebelumnya dengan membawa flash alert sukses
        return back()->with('success', "\"{$city->name}\" sekarang berstatus {$tipe}.");
    }

    // Hapus kota beserta rute jaraknya
    public function destroy(City $city)
    {
        // 1. Bersihkan semua rute jarak di depot_distances yang terhubung dengan kota ini (baik sebagai City A atau City B)
        DB::table('depot_distances')
            ->where('city_a_id', $city->id)
            ->orWhere('city_b_id', $city->id)
            ->delete();

        // 2. Hapus data utama entitas kota dari database
        $city->delete();

        // 3. Alihkan kembali ke halaman indeks kota dengan membawa flash alert sukses
        return redirect()->route('cities.index')
            ->with('success', 'Kota berhasil dihapus beserta rute jaraknya.');
    }

    // Geocode nama kota -> [lat, lon] via Nominatim (open streetmap)
    private function geocodeCity(string $name): array
    {
        try {
            // 1. Lakukan request ke API Nominatim dengan batasan region Indonesia, format JSON, dan timeout 8 detik.
            //    *Catatan: User-Agent wajib diisi agar tidak diblokir/rate-limit oleh kebijakan OpenStreetMap.
            $resp = Http::withHeaders(['User-Agent' => 'XKargo/1.0'])
                ->timeout(8)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q'      => "{$name}, Indonesia",
                    'format' => 'json',
                    'limit'  => 1,
                ]);

            // 2. Validasi respon: Pastikan HTTP status 200 OK dan hasil pencarian tidak kosong
            if ($resp->ok() && count($resp->json()) > 0) {
                $place = $resp->json()[0];

                // 3. Konversi nilai string dari API menjadi tipe data Float untuk presisi koordinat di database/peta
                return [(float) $place['lat'], (float) $place['lon']];
            }
        } catch (\Exception $e) {
            // 4. Tangkap error jaringan atau API gagal merespon tanpa menghentikan jalannya aplikasi
            // Log::warning("Geocoding gagal untuk {$name}: " . $e->getMessage());
        }

        // 5. Mekanisme Fallback: Kembalikan titik koordinat default jika API gagal/tidak menemukan kota tersebut
        return [-7.5360, 112.2384];
    }

    // Auto-fill jarak
    private function fillDistancesFromOsrm(City $newCity): void
    {
        // 1. Ambil semua kota lain yang aktif untuk dipasangkan dengan kota baru ini
        $others = City::where('id', '!=', $newCity->id)
            ->where('is_active', true)
            ->get();

        // Jika tidak ada kota lain di database, hentikan proses
        if ($others->isEmpty()) return;

        // 2. Format koordinat sesuai standar OSRM (Wajib: longitude,latitude dipisahkan dengan tanda ';')
        //    Menempatkan kota baru di indeks pertama (0) sebagai titik pusat awal
        $allCoords   = collect(["{$newCity->longitude},{$newCity->latitude}"]);
        $coordsOther = $others->map(fn($c) => "{$c->longitude},{$c->latitude}");
        $allCoords   = $allCoords->merge($coordsOther)->implode(';');

        // 3. Tarik parameter konfigurasi OSRM dari file config/services.php atau .env
        $osrmBase = config('services.osrm.base_url', 'https://router.project-osrm.org');
        $profile  = config('services.osrm.profile', 'driving');
        $speedKmh = (float) config('services.osrm.speed_kmh', 60);

        try {
            // 4. Request matriks tabel ke OSRM untuk mendapatkan durasi antar koordinat
            $resp = Http::timeout(15)
                ->get("{$osrmBase}/table/v1/{$profile}/{$allCoords}", [
                    'annotations' => 'duration',
                ]);

            // 5. FALLBACK 1: Jika HTTP gagal atau status response OSRM bukan 'Ok'
            if (!$resp->ok() || ($resp->json()['code'] ?? '') !== 'Ok') {
                foreach ($others as $other) {
                    // Hitung jarak matematis Haversine lalu kalikan koridor deviasi jalan raya (1.3)
                    $km = $this->haversineKm(
                        $newCity->latitude,
                        $newCity->longitude,
                        $other->latitude,
                        $other->longitude
                    ) * 1.3;

                    // Simpan atau update ke tabel depot_distances
                    $this->upsertDistance($newCity->id, $other->id, round($km, 2));
                }
                return;
            }

            // 6. PROSES DATA API: Ekstrak matriks durasi (dalam satuan detik)
            $durations = $resp->json()['durations'];
            foreach ($others as $idx => $other) {
                // Ambil durasi dari kota baru (indeks 0) ke kota tujuan berikutnya (indeks $idx + 1)
                $durSec = $durations[0][$idx + 1] ?? null;

                // Rumus Konversi: (Detik / 3600 detik) = Jam * Kecepatan Truk = Jarak (Km)
                $km = $durSec !== null
                    ? round(($durSec / 3600) * $speedKmh, 2)
                    : round($this->haversineKm(
                        $newCity->latitude,
                        $newCity->longitude,
                        $other->latitude,
                        $other->longitude
                    ) * 1.3, 2); // Fallback lokal jika salah satu koordinat di dalam array menghasilkan durasi null

                // Simpan hasil kalkulasi final ke database
                $this->upsertDistance($newCity->id, $other->id, $km);
            }
        } catch (\Exception $e) {
            // 7. FALLBACK 2: Pengaman total jika terjadi crash server, kendala internet, atau API OSRM down.
            //    Menjamin proses pendaftaran kota baru tidak terputus di tengah jalan.
            foreach ($others as $other) {
                $km = $this->haversineKm(
                    $newCity->latitude,
                    $newCity->longitude,
                    $other->latitude,
                    $other->longitude
                ) * 1.3;

                $this->upsertDistance($newCity->id, $other->id, round($km, 2));
            }
        }
    }

    private function upsertDistance(int $aId, int $bId, float $km): void
    {
        // 1. Lakukan perulangan dua kali dengan membalik pasangan ID kota [[A, B], [B, A]]
        //    Langkah ini krusial agar algoritma pencarian rute nantinya bisa membaca jarak dari arah manapun
        foreach ([[$aId, $bId], [$bId, $aId]] as [$c1, $c2]) {
            
            // 2. Eksekusi klausa updateOrCreate untuk mengamankan data dari error Duplikasi Unique Key
            //    Sistem akan mencari apakah kombinasi city_a dan city_b sudah terdaftar:
            //    - Jika SUDAH ADA: Maka kolom 'distance_km' akan diperbarui (update) dengan jarak terbaru
            //    - Jika BELUM ADA: Maka sistem akan membuat baris baru (insert) ke tabel depot_distances
            DepotDistance::updateOrCreate(
                ['city_a_id' => $c1, 'city_b_id' => $c2], // Kriteria pencarian composite key unik
                ['distance_km' => $km]                    // Nilai yang diisi atau diperbarui
            );
        }
    }

    /**
     * Menghitung jarak garis lurus ("as the crow flies") antara dua titik koordinat di bumi.
     * * Menggunakan Formula Haversine berdasarkan kelengkungan bumi (titik sferis).
     * Hasil dari fungsi ini adalah jarak terpendek dalam satuan Kilometer (Km).
     *
     * @param  float  $lat1  Latitude titik asal (titik A).
     * @param  float  $lon1  Longitude titik asal (titik A).
     * @param  float  $lat2  Latitude titik tujuan (titik B).
     * @param  float  $lon2  Longitude titik tujuan (titik B).
     * @return float         Jarak bersih dalam satuan Kilometer (Km).
     */
    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // 1. Tentukan Konstanta R (Jari-jari rata-rata planet bumi = 6.371 Kilometer)
        $R    = 6371;

        // 2. Konversikan koordinat lintang (Latitude) dari satuan Derajat ke Radian
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);

        // 3. Hitung selisih jarak (Delta) antara titik Lintang dan titik Bujur dalam satuan Radian
        $dphi = deg2rad($lat2 - $lat1);
        $dlam = deg2rad($lon2 - $lon1);

        // 4. Hitung nilai 'a' (Kuadrat dari setengah panjang tali busur antara dua titik)
        //    menggunakan hukum sinus dan kosinus sferis
        $a    = sin($dphi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dlam / 2) ** 2;

        // 5. Hitung jarak angular akhir (c = 2 * arcsin(sqrt(a))) lalu kalikan dengan jari-jari bumi
        return $R * 2 * asin(sqrt($a));
    }
}
