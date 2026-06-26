<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulation_results', function (Blueprint $table) {
            $table->id();
            $table->date('run_date');
            
            // Relasi ke truk mana yang membawa rute ini
            $table->foreignId('truck_id')->constrained('trucks')->onDelete('cascade');
            
            // Kolom JSON untuk menyimpan array rute dan grafik PSO
            $table->json('route_json');
            $table->json('gbest_curve_json')->nullable(); // Boleh kosong jika tidak ada grafik
            
            // Detail kalkulasi
            $table->decimal('total_weight_kg', 10, 2);
            $table->decimal('total_volume_m3', 10, 2);
            $table->decimal('tariff_total', 15, 2);
            $table->decimal('fuel_cost', 15, 2);
            $table->decimal('net_profit', 15, 2);
            
            $table->timestamps(); // Ini yang akan diurus otomatis oleh Laravel
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_results');
    }
};