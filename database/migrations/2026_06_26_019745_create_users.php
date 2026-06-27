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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // PERTAHANKAN DARI LARAVEL: Pastikan username tidak ada yang kembar
            $table->string('username')->unique();

            $table->string('password_hash');

            // REVISI DARI SQL: Gunakan enum agar role dibatasi hanya 'admin' atau 'operator'
            $table->enum('role', ['admin', 'operator'])->default('admin');

            $table->timestamps();
        });

        // Hapus atau biarkan blok password_reset_tokens dan sessions
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
