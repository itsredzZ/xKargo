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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('delivery_orders')->onDelete('cascade');
            $table->integer('quantity')->default(1); // Jumlah barang sejenis
            $table->string('name'); // Nama atau jenis barang
            $table->decimal('weight_kg', 10, 2); // Berat barang
            $table->decimal('length_cm', 8, 2);  // Panjang barang 
            $table->decimal('width_cm', 8, 2);   // Lebar barang 
            $table->decimal('height_cm', 8, 2);  // Tinggi barang 
            $table->string('status')->default('pending'); // pending | delivered | carryover
            $table->boolean('is_carryover')->default(false); // flag prioritas PSO
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
