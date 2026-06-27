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
        Schema::create('depot_distances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('city_a_id')->constrained('cities')->onDelete('cascade');
            $table->foreignId('city_b_id')->constrained('cities')->onDelete('cascade');

            $table->decimal('distance_km', 10, 2);
            $table->integer('duration_minutes')->nullable();

            $table->timestamps();

            // REVISI: Mencegah adanya rute ganda untuk pasangan kota yang sama
            $table->unique(['city_a_id', 'city_b_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depot_distances');
    }
};
