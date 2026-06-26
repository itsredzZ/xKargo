<?php

namespace Database\Seeders;

use App\Models\DepotDistance;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Jalankan dengan: php artisan db:seed
     *
     * Urutan penting — ada dependensi foreign key:
     *   1. UserSeeder        → tabel users (tidak ada FK)
     *   2. CitySeeder        → tabel cities (tidak ada FK)
     *   3. DepotDistanceSeeder → butuh cities sudah ada
     *   4. TruckSeeder       → butuh cities sudah ada
     *   5. SettingSeeder     → tidak ada FK
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CitySeeder::class,
            DepotDistanceSeeder::class,
            TruckSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
