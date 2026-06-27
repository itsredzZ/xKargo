<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\DeliveryOrder;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PsoTestSeeder extends Seeder
{
    public function run()
    {
        // Nonaktifkan FK check sementara agar tidak error saat seeder
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DeliveryOrder::truncate();
        Item::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $depots = City::where('is_depot', 1)->where('is_active', 1)->get();
        $nonDepots = City::where('is_depot', 0)->pluck('id')->toArray();
        $today = Carbon::today()->toDateString();

        $namaBarang = [
            'Kardus Elektronik', 'Paket Pakaian', 'Sparepart Mesin', 'Keramik Lantai',
            'Bahan Bangunan', 'ATK Kantor', 'Makanan Kering', 'Obat-obatan',
            'Komponen Plastik', 'Kaca Tempered', 'Besi Ringan', 'Cat Gallon'
        ];

        foreach ($depots as $depot) {
            // Buat 10-12 item fisik untuk setiap depot hari ini
            $itemCount = rand(10, 12);
            
            // Tujuan bisa kota biasa ATAU depot lain
            $possibleDests = array_merge(
                $nonDepots, 
                $depots->where('id', '!=', $depot->id)->pluck('id')->toArray()
            );

            for ($i = 0; $i < $itemCount; $i++) {
                $p = rand(30, 190); // Panjang cm (max 200 supaya muat box)
                $l = rand(20, 120); // Lebar cm
                $t = rand(15, 120); // Tinggi cm
                $w = rand(10, 150); // Berat fisik kg
                $destId = $possibleDests[array_rand($possibleDests)];

                // 1. Buat Item Fisik
                $item = Item::create([
                    'name' => $namaBarang[array_rand($namaBarang)] . ' ' . rand(100, 999),
                    'length_cm' => $p,
                    'width_cm' => $l,
                    'height_cm' => $t,
                    'weight_kg' => $w,
                    'is_carryover' => false,
                ]);

                // 2. Buat Order Pengiriman yang mengarah ke Item fisik tersebut
                DeliveryOrder::create([
                    'item_id' => $item->id,
                    'origin_depot_id' => $depot->id,
                    'destination_city_id' => $destId,
                    'quantity' => 1,
                    'order_date' => $today,
                    'status' => 'pending',
                ]);
            }
        }

        $this->command->info("Berhasil generate " . Item::count() . " item untuk testing PSO!");
    }
}