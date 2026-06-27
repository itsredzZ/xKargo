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
            $table->string('batch_id')->index();
            $table->date('run_date');
            $table->foreignId('truck_id')->constrained('trucks')->onDelete('cascade');
            $table->json('route_json');
            $table->json('gbest_curve_json')->nullable();
            $table->decimal('total_weight_kg', 10, 2);
            $table->decimal('total_volume_m3', 10, 2);
            $table->decimal('total_distance_km', 10, 2)->nullable();
            $table->decimal('tariff_total', 15, 2);
            $table->decimal('fuel_cost', 15, 2);
            $table->decimal('net_profit', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_results');
    }
};
