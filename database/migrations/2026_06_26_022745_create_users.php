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
            $table->string('username')->unique();      // Sesuai dengan getAuthIdentifierName()
            $table->string('password_hash');           // Sesuai dengan $fillable temanmu
            $table->string('role')->default('operator'); // Sesuai dengan $fillable temanmu
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
