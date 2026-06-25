<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\RiwayatLaporan;
use App\Livewire\Settings;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Halaman yang butuh login
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Halaman kamu
    Route::get('/riwayat',  RiwayatLaporan::class)->name('riwayat');
    Route::get('/settings', Settings::class)->name('settings');

    // Profile bawaan Breeze — boleh tetap ada
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';