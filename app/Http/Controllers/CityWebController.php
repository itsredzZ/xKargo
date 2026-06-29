<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\DepotDistance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class CityWebController extends Controller
{
    public function index()
    {
        $cities = City::orderBy('name', 'asc')->paginate(10);
        $allCities = City::all();
        return view('cities.index', compact('cities', 'allCities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:100', 'unique:cities,name'],
            'is_depot' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama kota wajib diisi.',
            'name.unique'   => 'Kota ini sudah terdaftar.',
        ]);

        // ── 1. Geocoding via Nominatim (OpenStreetMap, gratis) ──
        [$lat, $lon] = $this->geocodeCity($request->name);

        // ── 2. Simpan kota baru ──
        $city = City::create([
            'name'      => trim($request->name),
            'latitude'  => $lat,
            'longitude' => $lon,
            'is_depot'  => $request->boolean('is_depot'),
            'is_active' => true,
        ]);

        // ── 3. Auto-fill jarak ke semua kota lain via OSRM ──
        $this->fillDistancesFromOsrm($city);

        return redirect()->route('cities.index')
            ->with('success', "Kota \"{$city->name}\" berhasil ditambahkan.");
    }

    /**
     * Toggle is_depot: depot ↔ reguler
     */
    public function toggleDepot(City $city)
    {
        $city->update(['is_depot' => !$city->is_depot]);

        $tipe = $city->is_depot ? 'Depot' : 'Kota Reguler';
        return back()->with('success', "\"{$city->name}\" sekarang berstatus {$tipe}.");
    }

    public function destroy(City $city)
    {
        DB::table('depot_distances')
            ->where('city_a_id', $city->id)
            ->orWhere('city_b_id', $city->id)
            ->delete();

        $city->delete();

        return redirect()->route('cities.index')
            ->with('success', 'Kota berhasil dihapus beserta rute jaraknya.');
    }

    // ─────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────

    /**
     * Geocode nama kota → [lat, lon] via Nominatim.
     * Fallback ke titik tengah Jawa Timur jika gagal.
     */
    private function geocodeCity(string $name): array
    {
        try {
            $resp = Http::withHeaders(['User-Agent' => 'XKargo/1.0'])
                ->timeout(8)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q'      => "{$name}, Indonesia",
                    'format' => 'json',
                    'limit'  => 1,
                ]);

            if ($resp->ok() && count($resp->json()) > 0) {
                $place = $resp->json()[0];
                return [(float) $place['lat'], (float) $place['lon']];
            }
        } catch (\Exception $e) {
            // Log::warning("Geocoding gagal untuk {$name}: " . $e->getMessage());
        }

        // Fallback: pusat Jawa Timur
        return [-7.5360, 112.2384];
    }

    /**
     * Hitung jarak dari $newCity ke semua kota lain via OSRM Table API,
     * lalu simpan ke depot_distances (upsert kedua arah).
     */
    private function fillDistancesFromOsrm(City $newCity): void
    {
        $others = City::where('id', '!=', $newCity->id)
            ->where('is_active', true)
            ->get();

        if ($others->isEmpty()) return;

        // Koordinat dalam format OSRM: lon,lat (GeoJSON convention)
        $allCoords   = collect(["{$newCity->longitude},{$newCity->latitude}"]);
        $coordsOther = $others->map(fn($c) => "{$c->longitude},{$c->latitude}");
        $allCoords   = $allCoords->merge($coordsOther)->implode(';');

        $osrmBase = config('services.osrm.base_url', 'https://router.project-osrm.org');
        $profile  = config('services.osrm.profile', 'driving');
        $speedKmh = (float) config('services.osrm.speed_kmh', 60);

        try {
            $resp = Http::timeout(15)
                ->get("{$osrmBase}/table/v1/{$profile}/{$allCoords}", [
                    'annotations' => 'duration',
                ]);

            if (!$resp->ok() || ($resp->json()['code'] ?? '') !== 'Ok') {
                // Fallback Haversine × 1.3 untuk semua pasangan
                foreach ($others as $other) {
                    $km = $this->haversineKm(
                        $newCity->latitude,
                        $newCity->longitude,
                        $other->latitude,
                        $other->longitude
                    ) * 1.3;
                    $this->upsertDistance($newCity->id, $other->id, round($km, 2));
                }
                return;
            }

            $durations = $resp->json()['durations']; // matrix N×N, index 0 = newCity
            foreach ($others as $idx => $other) {
                $durSec = $durations[0][$idx + 1] ?? null;
                $km = $durSec !== null
                    ? round(($durSec / 3600) * $speedKmh, 2)
                    : round($this->haversineKm(
                        $newCity->latitude,
                        $newCity->longitude,
                        $other->latitude,
                        $other->longitude
                    ) * 1.3, 2);

                $this->upsertDistance($newCity->id, $other->id, $km);
            }
        } catch (\Exception $e) {
            // OSRM timeout / unreachable → Haversine fallback
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
        foreach ([[$aId, $bId], [$bId, $aId]] as [$c1, $c2]) {
            DepotDistance::updateOrCreate(
                ['city_a_id' => $c1, 'city_b_id' => $c2],
                ['distance_km' => $km]
            );
        }
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R    = 6371;
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1);
        $dlam = deg2rad($lon2 - $lon1);
        $a    = sin($dphi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dlam / 2) ** 2;
        return $R * 2 * asin(sqrt($a));
    }
}
