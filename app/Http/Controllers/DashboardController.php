<?php

namespace App\Http\Controllers;

use App\Models\Truck;
use App\Models\City;
use App\Models\SimulationResult;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil truk aktif untuk perhitungan statistik
        $trucks = Truck::where('is_active', true)->get();

        // 1. REVISI: Menggunakan casting 'sum' untuk hasil yang lebih bersih
        $stats = [
            'trucks_total'       => $trucks->count(),
            'trucks_available'   => $trucks->where('operational_status', 'available')->count(),
            'trucks_on_duty'     => $trucks->where('operational_status', 'on_duty')->count(),
            'trucks_maintenance' => $trucks->where('operational_status', 'maintenance')->count(),
            'depots_active'      => City::depot()->count(),
            'cities_total'       => City::active()->count(),
            
            // 2. REVISI: Menggunakan created_at bawaan Laravel (karena kita pakai migrasi baru)
            'simulations_today'  => SimulationResult::whereDate('created_at', today())->count(),
            'profit_today'       => (float) SimulationResult::whereDate('created_at', today())->sum('net_profit'),
        ];

        // 3. 5 hasil PSO terbaru (menggunakan relasi yang sudah kita definisikan)
        $recentSimulations = SimulationResult::with('truck')
            ->orderByDesc('created_at') // Urutkan berdasarkan waktu input terbaru
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentSimulations'));
    }
}