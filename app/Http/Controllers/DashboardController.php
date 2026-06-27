<?php

namespace App\Http\Controllers;

use App\Models\Truck;
use App\Models\City;
use App\Models\SimulationResult;
use App\Models\Item;
use App\Models\DeliveryOrder;
use App\Models\CarryoverItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Default values aman
        $stats = [
            'profit_today'         => 0,
            'items_delivered'      => 0,
            'carryover_pending'    => 0,
            'trucks_available'     => 0,
            'trucks_total'         => 0,
            'trucks_on_duty'       => 0,
            'trucks_maintenance'   => 0,
            'depots_active'        => 0,
            'cities_total'         => 0,
            'simulations_today'    => 0,
        ];
        
        $profitHistory = collect();
        $truckPositions = collect();
        $recentActivities = collect();
        $recentSimulations = collect();
        $pendingCarryovers = collect();

        // ============================================
        // 1. PROFIT HARI INI (Aman, query sebelumnya berhasil)
        // ============================================
        try {
            $stats['profit_today'] = (float) SimulationResult::whereDate('run_date', $today)->sum('net_profit');
            $stats['simulations_today'] = SimulationResult::whereDate('run_date', $today)->count();
        } catch (\Exception $e) {}

        // ============================================
        // 2. BARANG TERKIRIM (Bisa error kalau kolom 'status' belum ada)
        // ============================================
        try {
            $stats['items_delivered'] = DB::table('items')
                ->join('delivery_orders', 'items.order_id', '=', 'delivery_orders.id')
                ->where('items.status', 'terkirim')
                ->whereDate('delivery_orders.order_date', $today)
                ->count();
        } catch (\Exception $e) {
            $stats['items_delivered'] = 0; // Gagal = anggap 0
        }

        // ============================================
        // 3. CARRY-OVER (Bisa error kalau kolom 'resolved' belum ada)
        // ============================================
        try {
            $stats['carryover_pending'] = CarryoverItem::where('resolved', false)->count();
        } catch (\Exception $e) {
            try {
                // Fallback: coba kalau namanya 'processed'
                $stats['carryover_pending'] = CarryoverItem::where('processed', false)->count();
            } catch (\Exception $e2) {
                $stats['carryover_pending'] = 0;
            }
        }

        // ============================================
        // 4. TRUK (Bisa error kalau kolom 'operational_status' belum ada)
        // ============================================
        try {
            $stats['trucks_total'] = Truck::where('is_active', true)->count();
            $stats['trucks_available'] = Truck::where('is_active', true)->where('operational_status', 'available')->count();
            $stats['trucks_on_duty'] = Truck::where('is_active', true)->where('operational_status', 'on_duty')->count();
            $stats['trucks_maintenance'] = Truck::where('is_active', true)->where('operational_status', 'maintenance')->count();
        } catch (\Exception $e) {
            // Kalau operational_status tidak ada, hitung semua truk aktif sebagai "tersedia"
            try {
                $stats['trucks_total'] = Truck::count();
                $stats['trucks_available'] = $stats['trucks_total'];
            } catch (\Exception $e2) {}
        }

        // ============================================
        // 5. KOTA & DEPOT
        // ============================================
        try {
            $stats['cities_total'] = City::where('is_active', true)->count();
            $stats['depots_active'] = City::where('is_depot', true)->where('is_active', true)->count();
        } catch (\Exception $e) {}

        // ============================================
        // 6. PROFIT HISTORY 7 HARI
        // ============================================
        try {
            $profitHistory = SimulationResult::select(
                    'run_date',
                    DB::raw('SUM(tariff_total) as total_tariff'),
                    DB::raw('SUM(fuel_cost) as total_fuel'),
                    DB::raw('SUM(net_profit) as total_profit'),
                    DB::raw('COUNT(DISTINCT truck_id) as trucks_used')
                )
                ->where('run_date', '>=', $today->copy()->subDays(6))
                ->groupBy('run_date')
                ->orderBy('run_date', 'desc')
                ->get();
        } catch (\Exception $e) {}

        // ============================================
        // 7. POSISI TRUK PER DEPOT
        // ============================================
        try {
            $truckPositions = DB::table('trucks')
                ->join('cities', 'trucks.current_city_id', '=', 'cities.id')
                ->select('cities.name as depot_name', DB::raw('COUNT(trucks.id) as truck_count'))
                ->where('trucks.is_active', true)
                ->groupBy('trucks.current_city_id', 'cities.name')
                ->orderByDesc('truck_count')
                ->get();
        } catch (\Exception $e) {}

        // ============================================
        // 8. LOG AKTIVITAS
        // ============================================
        try {
            $recentSims = DB::table('simulation_results')
                ->leftJoin('trucks', 'simulation_results.truck_id', '=', 'trucks.id')
                ->orderByDesc('simulation_results.created_at')
                ->limit(3)
                ->get(['simulation_results.*', 'trucks.plate_number']);
            
            foreach ($recentSims as $sim) {
                $recentActivities->push((object) [
                    'time'     => Carbon::parse($sim->created_at),
                    'icon'     => '⚡',
                    'message'  => "PSO selesai untuk " . ($sim->plate_number ?? 'Truk #' . $sim->truck_id) . " — Profit: Rp " . number_format($sim->net_profit, 0),
                ]);
            }
        } catch (\Exception $e) {}

        // ============================================
        // 9. HASIL PSO TERBARU
        // ============================================
        try {
            $recentSimulations = DB::table('simulation_results')
                ->leftJoin('trucks', 'simulation_results.truck_id', '=', 'trucks.id')
                ->select('simulation_results.*', 'trucks.plate_number')
                ->orderByDesc('simulation_results.created_at')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {}

        // ============================================
        // 10. PENDING CARRYOVERS
        // ============================================
        try {
            $pendingCarryovers = DB::table('carryover_items')
                ->leftJoin('items', 'carryover_items.item_id', '=', 'items.id')
                ->where('carryover_items.resolved', false)
                ->orderByDesc('carryover_items.id')
                ->limit(5)
                ->get(['carryover_items.*', 'items.name as item_name']);
        } catch (\Exception $e) {
            try {
                // Fallback kolom 'processed'
                $pendingCarryovers = DB::table('carryover_items')
                    ->leftJoin('items', 'carryover_items.item_id', '=', 'items.id')
                    ->where('carryover_items.processed', false)
                    ->orderByDesc('carryover_items.id')
                    ->limit(5)
                    ->get(['carryover_items.*', 'items.name as item_name']);
            } catch (\Exception $e2) {}
        }

        return view('dashboard.index', compact(
            'stats',
            'profitHistory',
            'truckPositions',
            'recentActivities',
            'recentSimulations',
            'pendingCarryovers'
        ));
    }
}