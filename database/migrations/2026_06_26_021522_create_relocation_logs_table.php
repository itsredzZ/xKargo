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
        Schema::create('relocation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->constrained('trucks')->onDelete('cascade');
            
            // Relasi ke tabel cities untuk rute relokasi
            $table->foreignId('from_depot_id')->constrained('cities')->onDelete('cascade');
            $table->foreignId('to_depot_id')->constrained('cities')->onDelete('cascade');
            
            $table->decimal('relocation_cost', 15, 2);
            $table->date('relocation_date');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relocation_logs');
    }
};
