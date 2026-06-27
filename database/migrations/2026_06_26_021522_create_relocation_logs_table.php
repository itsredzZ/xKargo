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

            // REVISI: Untuk menghubungkan log ini dengan hasil simulasi tertentu
            $table->string('batch_id')->nullable()->index();

            $table->foreignId('truck_id')->constrained('trucks')->onDelete('cascade');

            $table->decimal('distance_km', 10, 2)->nullable();

            // Relasi ke tabel cities untuk rute relokasi
            $table->foreignId('from_depot_id')->constrained('cities')->onDelete('cascade');
            $table->foreignId('to_depot_id')->constrained('cities')->onDelete('cascade');

            // REVISI DARI SQL: Memberikan nilai default 0.00
            $table->decimal('relocation_cost', 15, 2)->default(0.00);

            // REVISI DARI SQL: Mencatat keputusan algoritma
            $table->enum('decision', ['relokasi', 'carryover']);

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
