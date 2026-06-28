{{-- resources/views/dashboard/index.blade.php --}}
{{-- Dashboard utama Laravel — SESUAI PROPOSAL SECTION B --}}

@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <style>
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .kpi-animate { animation: slideDown 0.35s ease-out both; }
        .kpi-animate:nth-child(1) { animation-delay: 0.05s; }
        .kpi-animate:nth-child(2) { animation-delay: 0.10s; }
        .kpi-animate:nth-child(3) { animation-delay: 0.15s; }
        .kpi-animate:nth-child(4) { animation-delay: 0.20s; }
    </style>
<div class="max-w-7xl mx-auto px-4 py-8 w-full font-sans space-y-8">

    {{-- HEADER BARU --}}
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-700 flex items-center justify-center shadow-md shadow-blue-100 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard XKargo</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('pso.orders') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl text-sm font-bold transition flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                Input Pesanan
            </a>
            <a href="{{ route('pso.run') }}" class="bg-blue-700 hover:bg-blue-800 active:bg-blue-900 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-blue-100 transition flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                Jalankan PSO
            </a>
        </div>
    </header>

    {{-- ============================================ --}}
    {{-- KPI CARDS - SESUAI PROPOSAL SECTION B --}}
    {{-- ============================================ --}}
  
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- 1. PROFIT (Tema Emerald) --}}
        <div class="kpi-animate bg-white rounded-2xl border border-emerald-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Profit Hari Ini</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-3xl font-black text-slate-900">
                    @if($stats['profit_today'] > 0)
                        Rp {{ number_format($stats['profit_today'] / 1000, 0) }}K
                    @else Rp 0 @endif
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5 font-medium">net setelah BBM & relokasi</p>
        </div>

        {{-- 2. TERKIRIM (Tema Biru) --}}
        <div class="kpi-animate bg-white rounded-2xl border border-blue-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold text-blue-600 uppercase tracking-widest">Barang Terkirim</p>
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-3xl font-black text-slate-900">{{ $stats['items_delivered'] }}</span>
                <span class="text-sm text-slate-400">paket</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5 font-medium">akumulasi hari ini</p>
        </div>

        {{-- 3. CARRY-OVER (Tema Amber) --}}
        <div class="kpi-animate bg-white rounded-2xl border border-amber-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold text-amber-600 uppercase tracking-widest">Carry-Over</p>
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-3xl font-black {{ $stats['carryover_pending'] > 0 ? 'text-amber-500' : 'text-slate-900' }}">
                    {{ $stats['carryover_pending'] }}
                </span>
            </div>
            <p class="text-[11px] mt-1.5 font-medium {{ $stats['carryover_pending'] > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                {{ $stats['carryover_pending'] > 0 ? 'wajib masuk truk hari ini' : 'aman, tidak ada antrean' }}
            </p>
        </div>

        {{-- 4. TRUK TERSEDIA (Tema Slate / Neutral) --}}
        <div class="kpi-animate bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Truk Tersedia</p>
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-1.5">
                {{-- Sesuaikan nama variabel stats truk kamu di sini --}}
                <span class="text-3xl font-black text-slate-900">{{ $stats['trucks_available'] ?? 0 }}</span>
                <span class="text-sm text-slate-400">unit</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5 font-medium">siap menerima muatan</p>
        </div>

    </div>

    {{-- WARNING CARRY-OVER --}}
    @if($stats['carryover_pending'] > 0)
    <div class="relative overflow-hidden bg-amber-50/80 border border-amber-200/80 rounded-2xl p-5 mb-6">
        <div class="flex items-start gap-4">
            <div class="p-2.5 bg-amber-500 text-white rounded-xl shadow-sm shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-bold text-amber-900">Perhatian: Ada {{ $stats['carryover_pending'] }} barang Carry-Over!</h3>
                <p class="text-xs text-amber-700 mt-0.5">Barang tertunda dari rute sebelumnya wajib diprioritaskan masuk ke dalam sistem hari ini.</p>
                
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach($pendingCarryovers as $co)
                        <span class="inline-flex items-center gap-1.5 bg-white/80 text-amber-900 px-2.5 py-1 rounded-lg text-xs font-semibold border border-amber-200/60 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            {{ $co->item_name ?? 'Item #' . $co->item_id }}
                            <span class="text-amber-600 font-normal">({{ $co->reason }})</span>
                        </span>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('pso.orders') }}" class="shrink-0 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm self-center">
                Proses Sekarang →
            </a>
        </div>
    </div>
    @endif

    {{-- ============================================ --}}
    {{-- BARIS 2: PROFIT KUMULATIF + POSISI TRUK --}}
    {{-- ============================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Rekap Profit --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-[0_2px_18px_-4px_rgba(0,0,0,0.05)] p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100/80">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Rekap Profit Kumulatif</h2>
                        <p class="text-xs text-slate-400">Riwayat efisiensi finansial hasil kalkulasi algoritma</p>
                    </div>
                </div>
            </div>

            @if($profitHistory->isEmpty())
                <div class="text-center py-12 border border-dashed border-slate-200 rounded-xl">
                    <p class="text-xs font-medium text-slate-400">Belum ada riwayat simulasi profit.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-y border-slate-100 text-[11px] font-bold tracking-wider uppercase text-slate-400 bg-slate-50/50">
                                <th class="py-3 px-4">Tanggal Eksekusi</th>
                                <th class="py-3 px-4 text-right">Armada</th>
                                <th class="py-3 px-4 text-right">Total Tarif</th>
                                <th class="py-3 px-4 text-right">Est. BBM</th>
                                <th class="py-3 px-4 text-right">Net Profit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-600">
                            @foreach($profitHistory as $row)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 text-slate-800 font-semibold">
                                    {{ \Carbon\Carbon::parse($row->run_date)->format('d M Y') }}
                                    @if(\Carbon\Carbon::parse($row->run_date)->isToday())
                                        <span class="ml-2 bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] px-2 py-0.5 rounded-full font-bold">TODAY</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums">{{ $row->trucks_used }} Unit</td>
                                <td class="py-3 px-4 text-right tabular-nums">Rp {{ number_format($row->total_tariff, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-rose-500 tabular-nums">- Rp {{ number_format($row->total_fuel, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right tabular-nums font-bold">
                                    <span class="px-2.5 py-1 rounded-lg {{ $row->total_profit >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-rose-50 text-rose-700 border border-rose-200/60' }}">
                                        Rp {{ number_format($row->total_profit, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Posisi Truk --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_2px_18px_-4px_rgba(0,0,0,0.05)] p-6 flex flex-col">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl border border-blue-100/80">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">Sebaran Armada</h2>
                    <p class="text-xs text-slate-400">Titik siaga truk saat ini</p>
                </div>
            </div>

            @if($truckPositions->isEmpty())
                <div class="text-center py-12 my-auto border border-dashed border-slate-200 rounded-xl">
                    <p class="text-xs text-slate-400 font-medium">Belum ada data depot.</p>
                </div>
            @else
                <div class="space-y-2.5 my-auto">
                    @foreach($truckPositions as $pos)
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50/70 border border-slate-100 hover:bg-slate-50 transition-all">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-blue-600 ring-4 ring-blue-100"></span>
                            <span class="text-xs font-bold text-slate-700">{{ $pos->depot_name }}</span>
                        </div>
                        <span class="text-xs font-bold text-slate-600 bg-white px-2.5 py-1 rounded-lg border border-slate-200/60 shadow-2xs tabular-nums">
                            {{ $pos->truck_count }} Unit
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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Log Aktivitas --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_2px_18px_-4px_rgba(0,0,0,0.05)] p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2.5 bg-purple-50 text-purple-600 rounded-xl border border-purple-100/80">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">Aktivitas Sistem</h2>
                    <p class="text-xs text-slate-400">Log kejadian & mutasi data terakhir</p>
                </div>
            </div>

            @if($recentActivities->isEmpty())
                <div class="text-center py-12 border border-dashed border-slate-200 rounded-xl">
                    <p class="text-xs text-slate-400 font-medium">Belum ada aktivitas tercatat.</p>
                </div>
            @else
                <div class="space-y-3 max-h-[240px] overflow-y-auto pr-1">
                    @foreach($recentActivities as $activity)
                    <div class="flex items-start gap-3.5 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200/60 flex items-center justify-center text-sm shrink-0 shadow-2xs">
                            {{ $activity->icon }}
                        </div>
                        <div class="flex-1 min-w-0 pt-0.5">
                            <p class="text-xs font-semibold text-slate-700 truncate">{{ $activity->message }}</p>
                            <p class="text-[10px] font-medium text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($activity->time)->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Status Armada --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_2px_18px_-4px_rgba(0,0,0,0.05)] p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100/80">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Status Kesiapan Truk</h2>
                        <p class="text-xs text-slate-400">Rasio utilisasi armada hari ini</p>
                    </div>
                </div>

                @php
                    $total = max($stats['trucks_total'], 1);
                    $avail = $stats['trucks_available'];
                    $duty  = $stats['trucks_on_duty'];
                    $maint = $stats['trucks_maintenance'];
                @endphp

                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1.5">
                            <span class="flex items-center gap-1.5 text-slate-600"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Standby (Tersedia)</span>
                            <span class="text-slate-800 tabular-nums">{{ $avail }} <span class="text-slate-400 font-normal">/ {{ $stats['trucks_total'] }}</span></span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 p-0.5">
                            <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ round(($avail/$total)*100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1.5">
                            <span class="flex items-center gap-1.5 text-slate-600"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Sedang Bertugas</span>
                            <span class="text-slate-800 tabular-nums">{{ $duty }} <span class="text-slate-400 font-normal">/ {{ $stats['trucks_total'] }}</span></span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 p-0.5">
                            <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ round(($duty/$total)*100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1.5">
                            <span class="flex items-center gap-1.5 text-slate-600"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Dalam Perbaikan</span>
                            <span class="text-slate-800 tabular-nums">{{ $maint }} <span class="text-slate-400 font-normal">/ {{ $stats['trucks_total'] }}</span></span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 p-0.5">
                            <div class="bg-rose-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ round(($maint/$total)*100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            @if($avail === 0 && $stats['trucks_total'] > 0)
            <div class="mt-6 bg-rose-50 border border-rose-200/80 rounded-xl p-3 flex items-center gap-2.5 text-xs text-rose-700 font-bold">
                <span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span>
                Kritis: Tidak ada armada standby untuk menjalankan PSO!
            </div>
            @endif
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- HASIL PSO TERBARU --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_2px_18px_-4px_rgba(0,0,0,0.05)] p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl border border-amber-100/80">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-800">Hasil Kalkulasi PSO Terbaru</h2>
                <p class="text-xs text-slate-400">Batch optimasi muatan yang siap diberangkatkan</p>
            </div>
        </div>

        @if ($recentSimulations->isEmpty())
            <div class="text-center py-8 border border-dashed border-slate-200 rounded-xl">
                <p class="text-xs text-slate-400 font-medium">Belum ada hasil simulasi.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5">
                @foreach ($recentSimulations as $sim)
                <div class="bg-slate-50/70 border border-slate-200/70 rounded-xl p-4 hover:bg-white hover:border-slate-300 hover:shadow-md transition-all duration-200 group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2.5">
                            <span class="text-xs font-bold text-slate-800 tracking-tight group-hover:text-indigo-600 transition-colors">{{ $sim->plate_number ?? 'Truk #'.$sim->truck_id }}</span>
                            <span class="text-[10px] font-bold bg-white text-slate-500 border border-slate-200 px-1.5 py-0.5 rounded shadow-2xs shrink-0">
                                {{ $sim->run_date ? \Carbon\Carbon::parse($sim->run_date)->format('d M') : '-' }}
                            </span>
                        </div>
                        <p class="text-[11px] font-medium text-slate-400 flex items-center gap-1">
                            ⚖️ Muatan: <span class="text-slate-600 font-bold tabular-nums">{{ number_format($sim->total_weight_kg, 0, ',', '.') }} kg</span>
                        </p>
                    </div>

                    <div class="mt-4 pt-2.5 border-t border-slate-200/60 flex items-baseline justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Profit</span>
                        <span class="text-xs font-extrabold tabular-nums {{ $sim->net_profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            Rp {{ number_format($sim->net_profit / 1000, 0, ',', '.') }}k
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- PSO ENGINE - AKSES CEPAT KE STREAMLIT --}}
    {{-- ============================================ --}}
    <div class="relative overflow-hidden bg-slate-900 rounded-2xl p-6 text-white border border-slate-800 shadow-xl mb-6">
        <div class="absolute -right-10 -top-10 w-60 h-60 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center shrink-0 text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h3 class="font-bold text-base tracking-tight text-white">PSO Core Engine</h3>
                        <span class="inline-flex items-center gap-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> Live Streamlit
                        </span>
                    </div>
                    <p class="text-slate-400 text-xs mt-0.5">Proses komputasi partikel & kalkulasi matriks rute berjalan di servis terpisah</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('items.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700 transition-all">
                    Database Barang
                </a>
                <a href="{{ route('pso.orders') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700 transition-all">
                    Input Pesanan
                </a>
                <a href="{{ route('pso.run') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Jalankan Simulasi
                </a>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- STATUS SETUP (EXPANDER) --}}
    {{-- ============================================ --}}
    <details class="group bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden transition-all">
        <summary class="px-6 py-4 cursor-pointer select-none text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-slate-800 bg-slate-50/50 flex items-center justify-between transition-colors">
            <span class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                Konfigurasi Parameter Lingkungan
            </span>
            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 bg-white border-t border-slate-100">
            <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-100">
                <p class="text-[11px] font-semibold text-slate-400">Kota Terdaftar</p>
                <p class="text-lg font-extrabold text-slate-800 tabular-nums mt-0.5">{{ $stats['cities_total'] }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-100">
                <p class="text-[11px] font-semibold text-slate-400">Depot Aktif</p>
                <p class="text-lg font-extrabold text-slate-800 tabular-nums mt-0.5">{{ $stats['depots_active'] }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-100">
                <p class="text-[11px] font-semibold text-slate-400">Total Truk Terkoneksi</p>
                <p class="text-lg font-extrabold text-slate-800 tabular-nums mt-0.5">{{ $stats['trucks_total'] }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-100">
                <p class="text-[11px] font-semibold text-slate-400">Run PSO Hari Ini</p>
                <p class="text-lg font-extrabold text-indigo-600 tabular-nums mt-0.5">{{ $stats['simulations_today'] }} <span class="text-xs font-normal text-slate-400">kali</span></p>
            </div>
        </div>
    </details>

</div>
@endsection