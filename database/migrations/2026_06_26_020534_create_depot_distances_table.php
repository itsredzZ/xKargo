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
            
            // Foreign Keys untuk menghubungkan dua kota
            $table->foreignId('city_a_id')->constrained('cities')->onDelete('cascade');
            $table->foreignId('city_b_id')->constrained('cities')->onDelete('cascade');
            
            $table->decimal('distance_km', 10, 2);
            // Saya tambahkan duration_minutes opsional berjaga-jaga jika diperlukan OSRM API nantinya
            $table->integer('duration_minutes')->nullable(); 
            
            $table->timestamps();
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
