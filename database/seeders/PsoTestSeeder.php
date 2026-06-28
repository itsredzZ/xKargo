<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\DeliveryOrder;
use App\Models\City;
use Carbon\Carbon;

class PsoTestSeeder extends Seeder
{
    public function run()
    {
        // Bersihkan data lama (Tidak perlu SET FOREIGN_KEY_CHECKS lagi karena tidak ada circular FK!)
        DeliveryOrder::whereDate('order_date', Carbon::today())->delete();
        Item::whereHas('deliveryOrder', fn($q) => $q->whereDate('order_date', Carbon::today()))->delete();

        $depots = City::where('is_depot', 1)->where('is_active', 1)->get();
        $nonDepots = City::where('is_depot', 0)->where('is_active', 1)->pluck('id')->toArray();
        $today = Carbon::today()->toDateString();

        $namaBarang = ['Kardus Elektronik', 'Paket Pakaian', 'Sparepart Mesin', 'Keramik Lantai', 'Bahan Bangunan', 'ATK Kantor', 'Makanan Kering', 'Obat-obatan', 'Komponen Plastik', 'Kaca Tempered'];

        foreach ($depots as $depot) {
            $itemCount = rand(10, 12); // 10-12 pesanan per depot
            $possibleDests = array_merge($nonDepots, $depots->where('id', '!=', $depot->id)->pluck('id')->toArray());

            for ($i = 0; $i < $itemCount; $i++) {
                $destId = $possibleDests[array_rand($possibleDests)];

                // 1. Buat Header Order (Sekarang tidak ada item_id di sini!)
                $order = DeliveryOrder::create([
                    'origin_depot_id' => $depot->id,
                    'destination_city_id' => $destId,
                    'order_date' => $today,
                    'status' => 'pending',
                    'source' => 'manual',
                ]);

                // 2. Buat Item Fisik yang terhubung ke Order
                Item::create([
                    'order_id' => $order->id,
                    'name' => $namaBarang[array_rand($namaBarang)] . ' ' . rand(100, 999),
                    'length_cm' => rand(30, 190),
                    'width_cm' => rand(20, 120),
                    'height_cm' => rand(15, 120),
                    'weight_kg' => rand(10, 150),
                    'status' => 'menunggu',
                    'is_carryover' => false,
                ]);
            }
        }
        $this->command->info("Berhasil generate " . DeliveryOrder::whereDate('order_date', $today)->count() . " pesanan untuk testing PSO!");
    }
}