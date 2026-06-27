<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carryover_items', function (Blueprint $table) {
            $table->id();

            // Tetap gunakan DO id agar tahu rute asal-tujuan pesanan ini
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');

            $table->foreignId('order_id')
                ->constrained('delivery_orders')
                ->onDelete('cascade');

            // Dari revisi sebelumnya: untuk parsial muatan
            $table->integer('quantity')->default(1);

            // Dari SQL Anda: Menggunakan ENUM agar data rapi & konsisten
            $table->enum('reason', [
                'overflow_berat',
                'overflow_volume',
                'guillotine_gagal',
                'depot_tanpa_truk'
            ]);

            $table->date('carryover_date');

            // Dari SQL Anda: Penanda apakah carryover ini sudah diangkut di hari berikutnya
            $table->boolean('resolved')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carryover_items');
    }
};
