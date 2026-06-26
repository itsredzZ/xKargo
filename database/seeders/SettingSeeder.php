<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Seed parameter PSO dan operasional.
     *
     * Nilai identik dengan konstanta di pso_no2_last_boss.py.
     * Semua disimpan sebagai string — cast ke float/int saat dibaca oleh engine.
     *
     * CATATAN v3:
     *   tarif_dasar DIHAPUS — sekarang pakai tarif_per_km per truk.
     *   PSO gunakan: pendapatan = jarak_km × truck.tarif_per_km
     */
    public function run(): void
    {
        $settings = [
            // ── Parameter PSO ────────────────────────────────────────────────
            // Nilai sesuai konstanta di pso_no2_last_boss.py
            ['pso', 'w_max',           '0.9'],   // inersia awal (eksplorasi)
            ['pso', 'w_min',           '0.4'],   // inersia akhir (eksploitasi)
            ['pso', 'c1',              '2.0'],   // koefisien kognitif (Pbest)
            ['pso', 'c2',              '2.0'],   // koefisien sosial (Gbest)
            ['pso', 'n_partikel',      '30'],    // jumlah partikel
            ['pso', 'n_iterasi',       '100'],   // maksimum iterasi
            ['pso', 'early_stop_iter', '20'],    // stop jika tidak ada perbaikan N iterasi
            ['pso', 'base_seed',       '42'],    // seed random untuk reprodusibilitas

            // ── Parameter Operasional ─────────────────────────────────────────
            ['operasional', 'harga_solar',              '6800'],  // Rp/liter (2025)
            ['operasional', 'bbm_faktor',               '0.02'],  // tambahan L/km per 1000 kg muatan
            ['operasional', 'box_default_p_cm',         '200'],   // panjang default (CDD standar)
            ['operasional', 'box_default_l_cm',         '130'],   // lebar default
            ['operasional', 'box_default_t_cm',         '130'],   // tinggi default
            ['operasional', 'box_default_berat_max_kg', '5000'],  // kapasitas default (CDD)
        ];

        $inserted = 0;
        foreach ($settings as [$group, $key, $value]) {
            // upsert: insert jika belum ada, update value jika sudah ada
            $existing = DB::table('settings')
                ->where('param_group', $group)
                ->where('param_key', $key)
                ->first();

            if ($existing) {
                // Skip — jangan overwrite pengaturan yang sudah diubah admin
                continue;
            }

            DB::table('settings')->insert([
                'param_group' => $group,
                'param_key'   => $key,
                'param_value' => $value,
                'updated_at'  => now(),
            ]);

            $inserted++;
        }

        $this->command->info("✅ SettingSeeder: {$inserted} setting di-seed.");
    }
}
