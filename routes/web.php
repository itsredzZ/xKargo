<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PsoController;
use App\Http\Controllers\CityWebController;
use App\Http\Controllers\TruckWebController;
use App\Http\Controllers\DepotWebController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Include rute Auth bawaan Breeze (Login, Register, Password Reset)
require __DIR__.'/auth.php';

// Semua rute di bawah ini HANYA BISA diakses jika sudah Login
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── PSO Engine (Embed Streamlit) ─────────────────────────
    Route::get('/pso/orders',  [PsoController::class, 'orders'])->name('pso.orders');
    Route::get('/pso/items', [PsoController::class, 'items'])->name('pso.items');
    Route::get('/pso/run',     [PsoController::class, 'run'])->name('pso.run');
    Route::get('/pso/results', [PsoController::class, 'results'])->name('pso.results');

    // ── Master Data (Laravel Native) ─────────────────────────
    // Kota
    Route::get('/cities',         [CityWebController::class, 'index'])->name('cities.index');
    Route::post('/cities',        [CityWebController::class, 'store'])->name('cities.store');
    Route::patch('/cities/{id}',  [CityWebController::class, 'update'])->name('cities.update');
    Route::delete('/cities/{city}', [\App\Http\Controllers\CityWebController::class, 'destroy'])->name('cities.destroy');
    Route::patch('/cities/{city}/toggle-depot', [\App\Http\Controllers\CityWebController::class, 'toggleDepot'])->name('cities.toggleDepot');

    // Truk
    Route::get('/trucks',         [TruckWebController::class, 'index'])->name('trucks.index');
    Route::post('/trucks',        [TruckWebController::class, 'store'])->name('trucks.store');
    Route::put('/trucks/{id}',    [TruckWebController::class, 'update'])->name('trucks.update');
    Route::delete('/trucks/{id}', [TruckWebController::class, 'destroy'])->name('trucks.destroy');

    // Depot
    Route::get('/depot',          [DepotWebController::class, 'index'])->name('depot.index');
    Route::patch('/depot',        [DepotWebController::class, 'updateCapacity'])->name('depot.update');

    // Settings
    Route::get('/settings',       [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings',       [SettingsController::class, 'update'])->name('settings.update');

    // Profile (Bawaan Breeze)
    Route::get('/profile',        [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',      [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',     [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Controllers\RiwayatController;

// Riwayat & Laporan
Route::get('/riwayat',          [RiwayatController::class, 'index'])->name('riwayat.index');
Route::get('/riwayat/excel',    [RiwayatController::class, 'exportExcel'])->name('laporan.excel');
Route::get('/riwayat/pdf',      [RiwayatController::class, 'exportPdf'])->name('laporan.pdf');