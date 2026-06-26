<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruckSeeder extends Seeder
{
    /**
     * Seed 12 truk (2 per depot) dengan tipe dan tarif nyata.
     *
     * Tipe truk sesuai industri angkutan darat Indonesia 2025-2026:
     *   prime_mover : 28–30 ton | Rp 12.000–15.000/km
     *   tronton     : 15–20 ton | Rp  9.800–36.000/km
     *   fuso        : 7–8 ton   | Rp  9.800–36.000/km
     *   cdd_long    : 6 ton     | Rp  6.300–22.000/km
     *   cdd         : 4–8 ton   | Rp  6.000–19.000/km  ← paling banyak
     *   cde         : 2–3 ton   | Rp  4.900–14.000/km
     *   pickup      : 1–2 ton   | Rp  4.000/km (>25km)
     *   mobil_box   : ~1 ton    | Rp  4.500–9.000/km
     *
     * tarif_per_km = nilai representatif rute menengah Jawa Timur (100–300 km).
     * fuel_efficiency_km_per_liter = efisiensi BBM rata-rata per tipe.
     */
    public function run(): void
    {
        $cityMap = DB::table('cities')->pluck('id', 'name')->toArray();

        // [plate, max_kg, p_cm, l_cm, t_cm, depot, type, fuel_km_per_l, tarif_per_km]
        $trucks = [
            // Depot Surabaya — CDD + Fuso (volume tinggi, rute beragam)
            ['L 1001 AB',  5000.0, 280.0, 160.0, 155.0, 'Surabaya', 'cdd',    8.0, 10000.0],
            ['L 1002 CD',  7500.0, 400.0, 180.0, 170.0, 'Surabaya', 'fuso',   6.5, 15000.0],
            // Depot Malang — CDE + CDD (akses kota + antar kota)
            ['N 2001 EF',  2500.0, 200.0, 130.0, 130.0, 'Malang',   'cde',   10.0,  8000.0],
            ['N 2002 GH',  5000.0, 280.0, 160.0, 155.0, 'Malang',   'cdd',    7.5, 10000.0],
            // Depot Kediri — CDE + CDD (rute pedesaan)
            ['AG 3001 IJ', 2500.0, 200.0, 130.0, 130.0, 'Kediri',   'cde',   10.0,  8000.0],
            ['AG 3002 KL', 5000.0, 280.0, 160.0, 155.0, 'Kediri',   'cdd',    8.0, 10000.0],
            // Depot Madiun — CDE + CDD (rute pedesaan, unit lebih tua)
            ['AE 4001 MN', 2500.0, 200.0, 130.0, 130.0, 'Madiun',   'cde',    9.5,  8000.0],
            ['AE 4002 OP', 5000.0, 280.0, 160.0, 155.0, 'Madiun',   'cdd',    7.0, 10000.0],
            // Depot Jember — CDE + Pickup (last-mile + pelosok)
            ['P 5001 QR',  2500.0, 200.0, 130.0, 130.0, 'Jember',   'cde',   10.0,  8000.0],
            ['P 5002 ST',  1500.0, 180.0, 150.0,  40.0, 'Jember',   'pickup', 13.0,  5000.0],
            // Depot Tuban — CDE + CDD (jalur Pantura)
            ['S 6001 UV',  2500.0, 200.0, 130.0, 130.0, 'Tuban',    'cde',   10.0,  8000.0],
            ['S 6002 WX',  5000.0, 280.0, 160.0, 155.0, 'Tuban',    'cdd',    8.0, 10000.0],
        ];

        $inserted = 0;
        foreach ($trucks as [$plate, $maxKg, $p, $l, $t, $depot, $type, $fuel, $tarif]) {
            $depotId = $cityMap[$depot] ?? null;

            if (!$depotId) {
                $this->command->warn("⚠️  Depot '{$depot}' tidak ditemukan, truk '{$plate}' dilewati.");
                continue;
            }

            DB::table('trucks')->insertOrIgnore([
                'plate_number'                  => $plate,
                'max_weight_kg'                 => $maxKg,
                'length_cm'                     => $p,
                'width_cm'                      => $l,
                'height_cm'                     => $t,
                'home_depot_id'                 => $depotId,
                'current_city_id'               => $depotId,   // awalnya parkir di depot asal
                'is_active'                     => true,
                'operational_status'            => 'available',
                'truck_type'                    => $type,
                'fuel_efficiency_km_per_liter'  => $fuel,
                'tarif_per_km'                  => $tarif,
                'created_at'                    => now(),
                'updated_at'                    => now(),
            ]);

            $inserted++;
        }

        $this->command->info("✅ TruckSeeder: {$inserted} truk di-seed.");
    }
}
