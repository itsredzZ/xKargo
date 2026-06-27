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
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();

            // WAJIB: Plat nomor tidak boleh ada yang kembar
            $table->string('plate_number')->unique();
            $table->string('truck_type');

            // WAJIB: Untuk UI Dashboard Depot (available, on_duty, maintenance)
            $table->string('operational_status')->default('available');

            // WAJIB: Proteksi Relasi ke Kota
            $table->foreignId('home_depot_id')->constrained('cities')->onDelete('cascade');
            $table->foreignId('current_city_id')->nullable()->constrained('cities')->onDelete('set null');

            // WAJIB: Untuk perhitungan Guillotine Packing
            $table->decimal('max_weight_kg', 10, 2);
            $table->decimal('length_cm', 8, 2);
            $table->decimal('width_cm', 8, 2);
            $table->decimal('height_cm', 8, 2);

            // WAJIB: Untuk perhitungan Profitabilitas PSO Streamlit
            $table->decimal('fuel_efficiency_km_per_liter', 6, 2);
            $table->decimal('tarif_per_km', 12, 2)->default(0.00);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};
