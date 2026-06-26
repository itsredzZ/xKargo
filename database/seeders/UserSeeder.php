<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed akun admin default.
     *
     * ⚠️  GANTI PASSWORD setelah pertama login via halaman Settings!
     * Default: username=admin, password=admin123
     *
     * Catatan kolom:
     *   password_hash → nama kolom di skema XKargo (bukan 'password' Laravel default)
     */
    public function run(): void
    {
        $users = [
            [
                'username'      => 'admin',
                'password_hash' => Hash::make('admin123'),
                'role'          => 'admin',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ];

        foreach ($users as $user) {
            // insertOrIgnore = skip jika username sudah ada (idempotent)
            DB::table('users')->insertOrIgnore($user);
        }

        $this->command->info('✅ UserSeeder: ' . count($users) . ' user di-seed.');
    }
}
