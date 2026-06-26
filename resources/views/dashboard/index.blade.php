{{-- resources/views/dashboard/index.blade.php --}}
{{-- Dashboard utama Laravel — baca data dari db_xkargo (Pola 1: Shared DB) --}}

@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard XKargo</h1>
            <p class="text-gray-500 text-sm mt-0.5">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>
        <a href="{{ route('pso.run') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
            ⚡ Jalankan PSO Hari Ini
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-gray-500 text-xs uppercase tracking-wider">Truk Tersedia</p>
            <p class="text-3xl font-semibold text-green-600 mt-1">{{ $stats['trucks_available'] }}</p>
            <p class="text-gray-400 text-xs mt-1">dari {{ $stats['trucks_total'] }} armada aktif</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-gray-500 text-xs uppercase tracking-wider">Depot Aktif</p>
            <p class="text-3xl font-semibold text-blue-600 mt-1">{{ $stats['depots_active'] }}</p>
            <p class="text-gray-400 text-xs mt-1">dari {{ $stats['cities_total'] }} kota terdaftar</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-gray-500 text-xs uppercase tracking-wider">Run PSO Hari Ini</p>
            <p class="text-3xl font-semibold text-purple-600 mt-1">{{ $stats['simulations_today'] }}</p>
            <p class="text-gray-400 text-xs mt-1">simulasi selesai</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-gray-500 text-xs uppercase tracking-wider">Profit Hari Ini</p>
            <p class="text-3xl font-semibold text-emerald-600 mt-1">
                Rp {{ number_format($stats['profit_today'] / 1000, 0) }}K
            </p>
            <p class="text-gray-400 text-xs mt-1">net setelah BBM</p>
        </div>
    </div>

    {{-- Dua kolom: status truk + hasil PSO terbaru --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Status Armada --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-700">Status Armada</h2>
                <a href="{{ route('trucks.index') }}" class="text-blue-600 text-xs hover:underline">Lihat semua →</a>
            </div>

            {{-- Progress bar per status --}}
            @php
                $total = max($stats['trucks_total'], 1);
                $avail = $stats['trucks_available'];
                $duty  = $stats['trucks_on_duty'];
                $maint = $stats['trucks_maintenance'];
            @endphp

            <div class="space-y-3">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-green-600 font-medium">🟢 Tersedia</span>
                        <span class="text-gray-600">{{ $avail }} truk</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width:{{ $total > 0 ? ($avail/$total*100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-yellow-600 font-medium">🟡 Sedang Jalan</span>
                        <span class="text-gray-600">{{ $duty }} truk</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2">
                        <div class="bg-yellow-400 h-2 rounded-full" style="width:{{ $total > 0 ? ($duty/$total*100) : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-red-600 font-medium">🔴 Maintenance</span>
                        <span class="text-gray-600">{{ $maint }} truk</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2">
                        <div class="bg-red-400 h-2 rounded-full" style="width:{{ $total > 0 ? ($maint/$total*100) : 0 }}%"></div>
                    </div>
                </div>
            </div>

            @if($avail === 0)
            <div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-700">
                ⚠️ Tidak ada truk tersedia — PSO tidak bisa dijalankan hari ini.
            </div>
            @endif
        </div>

        {{-- Hasil PSO Terbaru --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-700">Hasil PSO Terbaru</h2>
                <a href="{{ route('pso.results') }}" class="text-blue-600 text-xs hover:underline">Lihat semua →</a>
            </div>

            @if ($recentSimulations->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <p class="text-2xl mb-2">⚡</p>
                    <p class="text-sm">Belum ada hasil PSO.</p>
                    <a href="{{ route('pso.run') }}" class="text-blue-600 text-xs hover:underline mt-1 inline-block">
                        Jalankan sekarang →
                    </a>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($recentSimulations as $sim)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $sim->truck?->plate_number ?? 'Truk #'.$sim->truck_id }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $sim->run_date->format('d M') }} •
                                {{ number_format($sim->total_weight_kg, 0) }} kg •
                                {{ number_format($sim->total_volume_m3, 2) }} m³
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold {{ $sim->net_profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                Rp {{ number_format($sim->net_profit / 1000, 0) }}K
                            </p>
                            <p class="text-xs text-gray-400">net profit</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Link cepat ke PSO Engine --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-xl p-5 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-lg">PSO Engine</h3>
                <p class="text-blue-200 text-sm mt-0.5">
                    Streamlit berjalan di background. Klik untuk buka halaman optimasi.
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('pso.orders') }}"
                   class="bg-white text-blue-800 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition">
                    📦 Input Pesanan
                </a>
                <a href="{{ route('pso.run') }}"
                   class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition border border-blue-400">
                    ⚡ Jalankan PSO
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
