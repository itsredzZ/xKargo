<?php
// routes/api.php
// ─────────────────────────────────────────────────────────────
// POLA 2 — REST API Routes
// Base URL: http://localhost:8000/api/...
//
// Semua route di grup 'streamlit' dilindungi middleware token.
// Route publik (tanpa token): tidak ada di production,
// tapi bisa dibuka untuk testing lokal dengan hapus middleware.
// ─────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\TruckController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\SimulationController;
use Illuminate\Support\Facades\Route;

// ── Group: semua endpoint dilindungi token Streamlit ─────────
Route::middleware('streamlit.token')->group(function () {

    // ── Trucks ──────────────────────────────────────────────
    Route::get('trucks',             [TruckController::class, 'index']);
    Route::get('trucks/available',   [TruckController::class, 'available']);  // khusus PSO
    Route::get('trucks/{id}',        [TruckController::class, 'show']);
    Route::post('trucks',            [TruckController::class, 'store']);
    Route::put('trucks/{id}',        [TruckController::class, 'update']);
    Route::patch('trucks/{id}/status', [TruckController::class, 'updateStatus']); // PSO dispatch

    // ── Cities & Matrix ──────────────────────────────────────
    Route::get('cities',         [CityController::class, 'index']);
    Route::get('cities/depots',  [CityController::class, 'depots']);
    Route::get('cities/matrix',  [CityController::class, 'matrix']);   // adj matrix untuk PSO
    Route::post('cities',        [CityController::class, 'store']);
    Route::patch('cities/{id}',  [CityController::class, 'update']);

    // ── Simulations (PSO Results) ────────────────────────────
    Route::get('simulations',           [SimulationController::class, 'index']);
    Route::post('simulations',          [SimulationController::class, 'store']);     // PSO simpan hasil
    Route::get('simulations/settings',  [SimulationController::class, 'settings']);  // PSO baca params
    Route::put('simulations/settings',  [SimulationController::class, 'updateSettings']); // Admin update
});

// ── Health check (tanpa token, untuk monitoring) ─────────────
Route::get('health', fn() => response()->json([
    'status'  => 'ok',
    'service' => 'XKargo Laravel API',
    'time'    => now()->toDateTimeString(),
]));
