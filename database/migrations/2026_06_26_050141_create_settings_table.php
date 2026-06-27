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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Kelompok parameter: 'pso' atau 'operasional'
            $table->enum('param_group', ['pso', 'operasional']);

            // Nama parameter, unik dalam satu grup (lihat unique di bawah)
            $table->string('param_key');

            // Nilai parameter — text agar fleksibel (angka desimal, string panjang, dsb.)
            $table->text('param_value')->nullable();

            $table->timestamps();

            // Constraint gabungan: satu param_key hanya boleh muncul sekali per grup.
            // Contoh valid  : ('pso','n_partikel') DAN ('operasional','n_partikel') → boleh
            // Contoh ditolak: ('pso','n_partikel') DAN ('pso','n_partikel')         → error
            $table->unique(['param_group', 'param_key'], 'uq_settings_group_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
