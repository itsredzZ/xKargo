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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();

            // Rute pengiriman
            $table->foreignId('origin_depot_id')
                  ->constrained('cities')
                  ->onDelete('cascade');

            $table->foreignId('destination_city_id')
                  ->constrained('cities')
                  ->onDelete('cascade');

            $table->date('order_date');

            // Status order untuk filter PSO: pending → optimized → delivered
            $table->string('status')->default('pending');

            // Sumber inputan: form manual atau upload Excel
            $table->enum('source', ['manual', 'excel_upload'])->default('manual');

            // Siapa yang menginput (nullable: bisa dihapus tanpa hapus order)
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->text('notes')->nullable();

            // Index gabungan: mempercepat query filter tanggal + status (dipakai PSO & dashboard)
            $table->index(['order_date', 'status']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
