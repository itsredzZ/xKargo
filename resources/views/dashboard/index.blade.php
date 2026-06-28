{{-- resources/views/dashboard/index.blade.php --}}
{{-- Dashboard utama Laravel — SESUAI PROPOSAL SECTION B --}}

@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard XKargo</h1>
            <p class="text-gray-500 text-sm mt-0.5">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pso.orders') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                📦 Input Pesanan
            </a>
            <a href="{{ route('pso.run') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                ⚡ Jalankan PSO
            </a>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- KPI CARDS - SESUAI PROPOSAL SECTION B --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- 1. PROFIT HARI INI --}}
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <p class="text-gray-500 text-xs uppercase tracking-wider">Profit Hari Ini</p>
                <span class="text-xl">💰</span>
            </div>
            <p class="text-3xl font-semibold text-emerald-600 mt-2">
                @if($stats['profit_today'] > 0)
                    Rp {{ number_format($stats['profit_today'] / 1000, 0) }}K
                @else
                    Rp 0
                @endif
            </p>
            <p class="text-gray-400 text-xs mt-1">net setelah BBM & relokasi</p>
        </div>

        {{-- 2. BARANG TERKIRIM --}}
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <p class="text-gray-500 text-xs uppercase tracking-wider">Barang Terkirim</p>
                <span class="text-xl">📦</span>
            </div>
            <p class="text-3xl font-semibold text-blue-600 mt-2">
                {{ $stats['items_delivered'] }}
            </p>
            <p class="text-gray-400 text-xs mt-1">paket hari ini</p>
        </div>

        {{-- 3. CARRY-OVER --}}
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <p class="text-gray-500 text-xs uppercase tracking-wider">Carry-Over</p>
                <span class="text-xl">⚠️</span>
            </div>
            <p class="text-3xl font-semibold {{ $stats['carryover_pending'] > 0 ? 'text-orange-600' : 'text-gray-400' }} mt-2">
                {{ $stats['carryover_pending'] }}
            </p>
            <p class="text-gray-400 text-xs mt-1">
                @if($stats['carryover_pending'] > 0)
                    <span class="text-orange-500">wajib masuk truk hari ini</span>
                @else
                    tidak ada
                @endif
            </p>
        </div>

        {{-- 4. TRUK TERSEDIA --}}
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <p class="text-gray-500 text-xs uppercase tracking-wider">Truk Tersedia</p>
                <span class="text-xl">🚛</span>
            </div>
            <p class="text-3xl font-semibold text-green-600 mt-2">
                {{ $stats['trucks_available'] }}
            </p>
            <p class="text-gray-400 text-xs mt-1">dari {{ $stats['trucks_total'] }} armada aktif</p>
        </div>
    </div>

    {{-- WARNING CARRY-OVER --}}
    @if($stats['carryover_pending'] > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <span class="text-2xl">⚠️</span>
            <div class="flex-1">
                <p class="font-semibold text-orange-800">
                    {{ $stats['carryover_pending'] }} barang carry-over harus diprioritaskan hari ini!
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($pendingCarryovers as $co)
                        <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded text-xs">
                            {{ $co->item_name ?? 'Item #' . $co->item_id }}
                            <span class="text-orange-500">({{ $co->reason }})</span>
                        </span>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('pso.orders') }}" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap">
                Proses →
            </a>
        </div>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- BARIS 2: PROFIT KUMULATIF + POSISI TRUK --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Rekap Profit --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-700 mb-4">📈 Rekap Profit Kumulatif</h2>

            @if($profitHistory->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <p class="text-sm">Belum ada riwayat profit. Jalankan PSO untuk memulai.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-2 px-3 text-gray-500 font-medium">Tanggal</th>
                                <th class="text-right py-2 px-3 text-gray-500 font-medium">Truk</th>
                                <th class="text-right py-2 px-3 text-gray-500 font-medium">Total Tarif</th>
                                <th class="text-right py-2 px-3 text-gray-500 font-medium">Biaya BBM</th>
                                <th class="text-right py-2 px-3 text-gray-500 font-medium">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profitHistory as $row)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="py-2.5 px-3 text-gray-700">
                                    {{ \Carbon\Carbon::parse($row->run_date)->format('d M Y') }}
                                    @if($row->run_date == now()->toDateString())
                                        <span class="ml-1 bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded text-xs">hari ini</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right text-gray-600">{{ $row->trucks_used }} unit</td>
                                <td class="py-2.5 px-3 text-right text-gray-600">Rp {{ number_format($row->total_tariff, 0) }}</td>
                                <td class="py-2.5 px-3 text-right text-red-500">- Rp {{ number_format($row->total_fuel, 0) }}</td>
                                <td class="py-2.5 px-3 text-right font-semibold {{ $row->total_profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    Rp {{ number_format($row->total_profit, 0) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Posisi Truk --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-700 mb-4">🚛 Posisi Truk</h2>

            @if($truckPositions->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <p class="text-sm">Belum ada data truk.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($truckPositions as $pos)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-sm text-gray-700">{{ $pos->depot_name }}</span>
                        </div>
                        <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full text-xs font-medium">
                            {{ $pos->truck_count }} truk
                        </span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- BARIS 3: LOG AKTIVITAS + STATUS ARMADA --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Log Aktivitas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-700 mb-4">📋 Log Aktivitas Terbaru</h2>

            @if($recentActivities->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <p class="text-sm">Belum ada aktivitas.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentActivities as $activity)
                    <div class="flex items-start gap-3">
                        <span class="text-lg mt-0.5">{{ $activity->icon }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 truncate">{{ $activity->message }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($activity->time)->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Status Armada --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-700 mb-4">📊 Status Armada</h2>

            @php
                $total = max($stats['trucks_total'], 1);
                $avail = $stats['trucks_available'];
                $duty  = $stats['trucks_on_duty'];
                $maint = $stats['trucks_maintenance'];
            @endphp

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="text-green-600 font-medium">🟢 Tersedia</span>
                        <span class="text-gray-600 font-medium">{{ $avail }} truk</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2.5">
                        <div class="bg-green-500 h-2.5 rounded-full" style="width:{{ ($avail/$total*100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="text-yellow-600 font-medium">🟡 Sedang Jalan</span>
                        <span class="text-gray-600 font-medium">{{ $duty }} truk</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2.5">
                        <div class="bg-yellow-400 h-2.5 rounded-full" style="width:{{ ($duty/$total*100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="text-red-600 font-medium">🔴 Maintenance</span>
                        <span class="text-gray-600 font-medium">{{ $maint }} truk</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2.5">
                        <div class="bg-red-400 h-2.5 rounded-full" style="width:{{ ($maint/$total*100) }}%"></div>
                    </div>
                </div>
            </div>

            @if($avail === 0 && $total > 0)
            <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-700">
                ⚠️ Tidak ada truk tersedia — PSO tidak bisa dijalankan.
            </div>
            @endif
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- HASIL PSO TERBARU --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="font-semibold text-gray-700 mb-4">⚡ Hasil PSO Terbaru</h2>

        @if ($recentSimulations->isEmpty())
            <div class="text-center py-6 text-gray-400">
                <p class="text-sm">Belum ada hasil PSO.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                @foreach ($recentSimulations as $sim)
                <div class="border border-gray-100 rounded-lg p-3 hover:border-blue-200 transition">
                    <p class="text-sm font-medium text-gray-700 truncate">{{ $sim->plate_number ?? 'Truk #'.$sim->truck_id }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $sim->run_date ? \Carbon\Carbon::parse($sim->run_date)->format('d M') : '-' }} • 
                        {{ number_format($sim->total_weight_kg, 0) }}kg
                    </p>
                    <p class="text-sm font-semibold mt-2 {{ $sim->net_profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        Rp {{ number_format($sim->net_profit / 1000, 0) }}K
                    </p>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- PSO ENGINE - AKSES CEPAT KE STREAMLIT --}}
    {{-- ============================================ --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-xl p-5 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h3 class="font-semibold text-lg">🔗 PSO Engine (Streamlit)</h3>
                <p class="text-blue-200 text-sm mt-0.5">
                    Optimasi & visualisasi algoritma berjalan di Streamlit
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('items.index') }}"
                   class="bg-blue-800/50 hover:bg-blue-600 text-blue-100 px-4 py-2 rounded-lg text-sm font-medium transition border border-blue-500/50 flex items-center gap-1.5">
                    📋 Database Barang
                </a>
                <a href="{{ route('pso.orders') }}"
                   class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-medium transition border border-white/30 flex items-center gap-1.5">
                    📦 Input Pesanan
                </a>
                <a href="{{ route('pso.run') }}"
                   class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition border border-blue-400 flex items-center gap-1.5">
                    ⚡ Jalankan PSO
                </a>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- STATUS SETUP (EXPANDER) --}}
    {{-- ============================================ --}}
    <details class="bg-gray-50 rounded-xl border border-gray-200">
        <summary class="px-5 py-3 cursor-pointer text-sm font-medium text-gray-600 hover:text-gray-800 transition">
            ℹ️ Status Setup Sistem
        </summary>
        <div class="px-5 pb-4 grid grid-cols-2 lg:grid-cols-4 gap-4 mt-2">
            <div class="bg-white rounded-lg p-3 border border-gray-100">
                <p class="text-xs text-gray-500">Kota Terdaftar</p>
                <p class="text-lg font-semibold text-gray-700">{{ $stats['cities_total'] }}</p>
            </div>
            <div class="bg-white rounded-lg p-3 border border-gray-100">
                <p class="text-xs text-gray-500">Depot Aktif</p>
                <p class="text-lg font-semibold text-gray-700">{{ $stats['depots_active'] }}</p>
            </div>
            <div class="bg-white rounded-lg p-3 border border-gray-100">
                <p class="text-xs text-gray-500">Total Truk</p>
                <p class="text-lg font-semibold text-gray-700">{{ $stats['trucks_total'] }}</p>
            </div>
            <div class="bg-white rounded-lg p-3 border border-gray-100">
                <p class="text-xs text-gray-500">Run PSO Hari Ini</p>
                <p class="text-lg font-semibold text-gray-700">{{ $stats['simulations_today'] }}</p>
            </div>
        </div>
    </details>

</div>
@endsection