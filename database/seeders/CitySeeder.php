<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Seed 31 kota Jawa Timur.
     *
     * Koordinat identik dengan CITY_COORDS di pso_no2_last_boss.py.
     * 6 kota bertanda is_depot=true dengan kapasitas parkir & gudang.
     *
     * Format kolom:
     *   name, latitude, longitude, is_depot, is_active,
     *   max_truck_capacity (null = tidak dibatasi),
     *   max_warehouse_kg   (null = tidak dibatasi)
     */
    public function run(): void
    {
        // [name, latitude, longitude, is_depot, max_truck_capacity, max_warehouse_kg]
        $cities = [
            // ── 6 Depot ───────────────────────────────────────────────────
            ['Surabaya',    -7.2575,  112.7521, true,  10, 50000.00],
            ['Malang',      -7.9797,  112.6304, true,   6, 30000.00],
            ['Kediri',      -7.8168,  111.9668, true,   4, 20000.00],
            ['Madiun',      -7.6298,  111.5239, true,   4, 20000.00],
            ['Jember',      -8.1845,  113.6680, true,   4, 20000.00],
            ['Tuban',       -6.8997,  112.0508, true,   4, 20000.00],
            // ── 25 Kota Non-Depot ─────────────────────────────────────────
            ['Banyuwangi',  -8.2192,  114.3691, false, null, null],
            ['Probolinggo', -7.7543,  113.2159, false, null, null],
            ['Pasuruan',    -7.6453,  112.9075, false, null, null],
            ['Mojokerto',   -7.4714,  112.4337, false, null, null],
            ['Blitar',      -8.0986,  112.1684, false, null, null],
            ['Situbondo',   -7.7062,  114.0083, false, null, null],
            ['Jombang',     -7.5519,  112.2384, false, null, null],
            ['Nganjuk',     -7.6047,  111.9003, false, null, null],
            ['Tulungagung', -8.0662,  111.9023, false, null, null],
            ['Lumajang',    -8.1354,  113.2235, false, null, null],
            ['Ngawi',       -7.4058,  111.4484, false, null, null],
            ['Bojonegoro',  -7.1519,  111.8814, false, null, null],
            ['Lamongan',    -7.1170,  112.4158, false, null, null],
            ['Gresik',      -7.1600,  112.6517, false, null, null],
            ['Sidoarjo',    -7.4561,  112.7183, false, null, null],
            ['Bangkalan',   -6.9070,  112.7427, false, null, null],
            ['Sampang',     -7.1956,  113.2478, false, null, null],
            ['Pamekasan',   -7.1578,  113.4742, false, null, null],
            ['Sumenep',     -6.9927,  113.8620, false, null, null],
            ['Bondowoso',   -7.9111,  113.8228, false, null, null],
            ['Ponorogo',    -7.8650,  111.4643, false, null, null],
            ['Magetan',     -7.6527,  111.3289, false, null, null],
            ['Pacitan',     -8.1980,  111.1033, false, null, null],
            ['Trenggalek',  -8.0490,  111.7132, false, null, null],
            ['Batu',        -7.8719,  112.5266, false, null, null],
        ];

        $rows = array_map(fn($c) => [
            'name'                 => $c[0],
            'latitude'             => $c[1],
            'longitude'            => $c[2],
            'is_depot'             => $c[3],
            'is_active'            => true,
            'max_truck_capacity'   => $c[4],
            'max_warehouse_kg'     => $c[5],
            'created_at'           => now(),
            'updated_at'           => now(),
        ], $cities);

        // insertOrIgnore = skip baris yang nama-nya sudah ada
        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('cities')->insertOrIgnore($chunk);
        }

        $depotCount = count(array_filter($cities, fn($c) => $c[3]));
        $this->command->info(
            "✅ CitySeeder: " . count($cities) . " kota di-seed ({$depotCount} depot)."
        );
    }
}
