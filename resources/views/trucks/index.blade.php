@extends('layouts.app')
@section('title', 'Armada Truk')

@section('content')

    <style>
        #modal-hapus-truk {
            display: none;
        }

        #modal-hapus-truk.aktif {
            display: flex;
        }

        #modal-edit-truk {
            display: none;
        }

        #modal-edit-truk.aktif {
            display: flex;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .kpi-animate {
            animation: slideDown 0.35s ease-out both;
        }

        .kpi-animate:nth-child(1) {
            animation-delay: 0.05s;
        }

        .kpi-animate:nth-child(2) {
            animation-delay: 0.10s;
        }

        .kpi-animate:nth-child(3) {
            animation-delay: 0.15s;
        }

        .kpi-animate:nth-child(4) {
            animation-delay: 0.20s;
        }

        .tab-filter {
            transition: all 0.15s ease;
        }

        .tab-filter.active {
            background: #1D4ED8;
            color: #ffffff;
            border-color: #1D4ED8;
        }

        .tab-filter:not(.active):hover {
            background: #F1F5F9;
        }

        #form-body {
            transition: max-height 0.25s ease, opacity 0.2s ease;
            overflow: hidden;
            max-height: 1000px;
            opacity: 1;
        }

        #form-body.collapsed {
            max-height: 0;
            opacity: 0;
        }

        #form-chevron {
            transition: transform 0.2s ease;
        }

        #form-chevron.collapsed {
            transform: rotate(-90deg);
        }

        tr[data-plat] {
            transition: background 0.1s;
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 py-8 w-full font-sans">

        {{-- Header --}}
        <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-blue-700 flex items-center justify-center shadow-md shadow-blue-100 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0
                                       01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1
                                       0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104
                                       0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Armada Truk</h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Pantau ketersediaan dan kelola setiap unit kendaraan pengiriman
                    </p>
                </div>
            </div>
            <span
                class="inline-flex items-center gap-1.5 text-xs font-semibold
                     bg-slate-100 text-slate-600 border border-slate-200
                     rounded-lg px-3 py-1.5 self-start sm:self-auto">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
                {{ $summary['total'] }} unit terdaftar
            </span>
        </header>

        {{-- Kartu Truk --}}
        @php
            $total = $summary['total'] ?: 1;
            $pctSiap = round(($summary['available'] / $total) * 100);
            $pctJalan = round(($summary['on_duty'] / $total) * 100);
            $pctBengkel = round(($summary['maintenance'] / $total) * 100);
        @endphp

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

            {{-- Total --}}
            <div class="kpi-animate bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Armada</p>
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl font-black text-slate-900">{{ $summary['total'] }}</span>
                    <span class="text-sm text-slate-400">unit</span>
                </div>
            </div>

            {{-- Siap Jalan --}}
            <div class="kpi-animate bg-white rounded-2xl border border-emerald-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Siap Jalan</p>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl font-black text-emerald-600">{{ $summary['available'] }}</span>
                    <span class="text-sm text-slate-400">unit</span>
                </div>
                <div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-1.5 rounded-full bg-emerald-400 transition-all" style="width: {{ $pctSiap }}%"></div>
                </div>
                <p class="text-[11px] text-emerald-600 mt-1.5 font-medium">{{ $pctSiap }}% dari armada</p>
            </div>

            {{-- Dalam Perjalanan --}}
            <div class="kpi-animate bg-white rounded-2xl border border-amber-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold text-amber-600 uppercase tracking-widest">Dalam Perjalanan</p>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1
                                           1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414
                                           3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5
                                           17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl font-black text-amber-500">{{ $summary['on_duty'] }}</span>
                    <span class="text-sm text-slate-400">unit</span>
                </div>
                <div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-1.5 rounded-full bg-amber-400 transition-all" style="width: {{ $pctJalan }}%"></div>
                </div>
                <p class="text-[11px] text-amber-600 mt-1.5 font-medium">{{ $pctJalan }}% sedang beroperasi</p>
            </div>

            {{-- Di Bengkel --}}
            <div class="kpi-animate bg-white rounded-2xl border border-red-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold text-red-500 uppercase tracking-widest">Di Bengkel</p>
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <svg class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl font-black text-red-500">{{ $summary['maintenance'] }}</span>
                    <span class="text-sm text-slate-400">unit</span>
                </div>
                <div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-1.5 rounded-full bg-red-400 transition-all" style="width: {{ $pctBengkel }}%"></div>
                </div>
                <p class="text-[11px] text-red-500 mt-1.5 font-medium">{{ $pctBengkel }}% tidak tersedia</p>
            </div>
        </div>

        {{-- Bar komposisi armada --}}
        @if ($summary['total'] > 0)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-6 py-4 mb-6">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
                    Ringkasan Armada Hari Ini
                </p>
                <div class="flex h-3 rounded-full overflow-hidden gap-0.5">
                    @if ($summary['available'] > 0)
                        <div class="bg-emerald-400 transition-all" style="width:{{ $pctSiap }}%"
                            title="Siap Jalan: {{ $summary['available'] }} unit"></div>
                    @endif
                    @if ($summary['on_duty'] > 0)
                        <div class="bg-amber-400 transition-all" style="width:{{ $pctJalan }}%"
                            title="Dalam Perjalanan: {{ $summary['on_duty'] }} unit"></div>
                    @endif
                    @if ($summary['maintenance'] > 0)
                        <div class="bg-red-400 transition-all" style="width:{{ $pctBengkel }}%"
                            title="Di Bengkel: {{ $summary['maintenance'] }} unit"></div>
                    @endif
                </div>
                <div class="flex items-center gap-5 mt-2.5">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-sm bg-emerald-400 flex-shrink-0"></span>
                        Siap Jalan
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-sm bg-amber-400 flex-shrink-0"></span>
                        Dalam Perjalanan
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-sm bg-red-400 flex-shrink-0"></span>
                        Di Bengkel
                    </div>
                </div>
            </div>
        @endif

        @if ($summary['available'] === 0)
            <div
                class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200
                    text-red-700 text-sm font-medium px-5 py-4 rounded-xl">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5 text-red-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732
                                   4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <strong class="font-bold">Tidak ada truk yang siap jalan.</strong>
                    Pengiriman hari ini tidak dapat dijadwalkan secara otomatis.
                    Ubah kondisi salah satu truk menjadi <em>Siap Jalan</em> melalui
                    tombol <strong>Ubah Data</strong> di tabel bawah.
                </div>
            </div>
        @endif

        {{-- Form daftarkan truk --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">

            <header
                class="bg-slate-50 px-6 py-4 border-b border-slate-100
                       flex items-center justify-between cursor-pointer select-none">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <h2 class="font-semibold text-slate-700 text-sm">DAFTAR TRUK BARU</h2>
                </div>
            </header>

            <div id="form-body">
                <div class="p-6">

                    @if ($errors->any())
                        <div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('trucks.store') }}" class="space-y-5">
                        @csrf

                        {{-- Baris 1: Plat · Jenis · Depot --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="plate_number"
                                    class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                    Nomor Plat Kendaraan
                                </label>
                                <input type="text" id="plate_number" name="plate_number"
                                    value="{{ old('plate_number') }}" placeholder="Contoh: L 1234 AB"
                                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3
                                       text-sm font-mono text-slate-900 focus:bg-white focus:ring-2
                                       focus:ring-blue-500 focus:border-blue-500 transition-all outline-none
                                       @error('plate_number') border-red-400 bg-red-50 @enderror"
                                    required autocomplete="off">
                            </div>
                            <div>
                                <label for="truck_type"
                                    class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                    Jenis Kendaraan
                                </label>
                                <select id="truck_type" name="truck_type"
                                    class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-4 pr-12 py-3
                                       text-sm text-slate-900 focus:bg-white focus:ring-2
                                       focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                    @foreach ($presets as $key => $p)
                                        <option value="{{ $key }}"
                                            {{ old('truck_type') === $key ? 'selected' : '' }}>
                                            {{ $p['label'] }} — maks {{ number_format($p['max_kg']) }} kg
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1.5">
                                    Kapasitas dan ukuran terisi otomatis sesuai jenis yang dipilih
                                </p>
                            </div>
                            <div>
                                <label for="home_depot_id"
                                    class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                    Depot Asal
                                </label>
                                <select id="home_depot_id" name="home_depot_id"
                                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3
                                       text-sm text-slate-900 focus:bg-white focus:ring-2
                                       focus:ring-blue-500 focus:border-blue-500 transition-all outline-none">
                                    @foreach ($depots as $depot)
                                        <option value="{{ $depot->id }}"
                                            {{ old('home_depot_id') == $depot->id ? 'selected' : '' }}>
                                            {{ $depot->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Baris 2: Spesifikasi (auto-isi dari preset) --}}
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3">
                                Spesifikasi
                            </p>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1.5">Muatan Maks (kg)</label>
                                    <input type="number" name="max_weight_kg" id="f_max_kg"
                                        value="{{ old('max_weight_kg', 1000) }}" min="100" step="50"
                                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5
                                           text-sm text-slate-900 focus:ring-2 focus:ring-blue-500
                                           focus:border-blue-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1.5">Solar (km/liter)</label>
                                    <input type="number" name="fuel_efficiency_km_per_liter" id="f_fuel"
                                        value="{{ old('fuel_efficiency_km_per_liter', 8) }}" min="1"
                                        max="30" step="0.5"
                                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5
                                           text-sm text-slate-900 focus:ring-2 focus:ring-blue-500
                                           focus:border-blue-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1.5">Panjang (cm)</label>
                                    <input type="number" name="length_cm" id="f_p"
                                        value="{{ old('length_cm', 200) }}" min="50"
                                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5
                                           text-sm text-slate-900 focus:ring-2 focus:ring-blue-500
                                           focus:border-blue-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1.5">Lebar (cm)</label>
                                    <input type="number" name="width_cm" id="f_l"
                                        value="{{ old('width_cm', 130) }}" min="50"
                                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5
                                           text-sm text-slate-900 focus:ring-2 focus:ring-blue-500
                                           focus:border-blue-500 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1.5">Tinggi (cm)</label>
                                    <input type="number" name="height_cm" id="f_t"
                                        value="{{ old('height_cm', 130) }}" min="50"
                                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5
                                           text-sm text-slate-900 focus:ring-2 focus:ring-blue-500
                                           focus:border-blue-500 outline-none transition-all">
                                </div>
                            </div>
                        </div>

                        <div class="pt-1">
                            <button type="submit"
                                class="bg-blue-700 hover:bg-blue-800 active:bg-blue-900 text-white
                                   px-6 py-3 rounded-xl text-sm font-bold shadow-md shadow-blue-100
                                   transition-all flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Daftarkan Truk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- Tabel --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <div
                class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">

                {{-- Filter --}}
                @php $fStat = request('status', 'all'); @endphp
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('trucks.index', array_merge(request()->query(), ['status' => 'all'])) }}"
                        class="tab-filter {{ $fStat === 'all' ? 'active' : '' }} px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 bg-white">
                        Semua
                    </a>
                    <a href="{{ route('trucks.index', array_merge(request()->query(), ['status' => 'available'])) }}"
                        class="tab-filter {{ $fStat === 'available' ? 'active' : '' }} px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 bg-white">
                        Siap Jalan
                    </a>
                    <a href="{{ route('trucks.index', array_merge(request()->query(), ['status' => 'on_duty'])) }}"
                        class="tab-filter {{ $fStat === 'on_duty' ? 'active' : '' }} px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 bg-white">
                        Dalam Perjalanan
                    </a>
                    <a href="{{ route('trucks.index', array_merge(request()->query(), ['status' => 'maintenance'])) }}"
                        class="tab-filter {{ $fStat === 'maintenance' ? 'active' : '' }} px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 bg-white">
                        Di Bengkel
                    </a>
                </div>

                <form method="GET" action="{{ route('trucks.index') }}" class="relative w-full sm:w-60">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input name="search" type="text" value="{{ request('search') }}"
                        placeholder="Cari plat atau kota..."
                        class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2 text-sm text-slate-700 bg-white focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none transition-all">
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[640px]">
                    <thead>
                        <tr
                            class="text-slate-400 uppercase text-[10px] tracking-widest
                               border-b border-slate-200 bg-slate-50">
                            <th class="px-6 py-3.5 font-bold">Kendaraan</th>
                            <th class="px-6 py-3.5 font-bold text-center">Kondisi</th>
                            <th class="px-6 py-3.5 font-bold text-right">Muatan Maks</th>
                            <th class="px-6 py-3.5 font-bold">Depot Asal</th>
                            <th class="px-6 py-3.5 font-bold">Lokasi Kini</th>
                            <th class="px-6 py-3.5 font-bold text-center w-28">Ubah Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="tbody-truk">

                        @forelse($trucks as $truck)
                            @php
                                [$statusBg, $statusDot, $statusLabel] = match ($truck->operational_status) {
                                    'available' => [
                                        'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'bg-emerald-500',
                                        'Siap Jalan',
                                    ],
                                    'on_duty' => [
                                        'bg-amber-50 text-amber-700 border-amber-200',
                                        'bg-amber-400',
                                        'Dalam Perjalanan',
                                    ],
                                    'maintenance' => [
                                        'bg-red-50 text-red-700 border-red-200',
                                        'bg-red-500',
                                        'Di Bengkel',
                                    ],
                                    default => [
                                        'bg-slate-100 text-slate-600 border-slate-200',
                                        'bg-slate-400',
                                        $truck->operational_status,
                                    ],
                                };
                            @endphp

                            <tr class="hover:bg-slate-50 transition-colors"
                                data-plat="{{ strtolower($truck->plate_number) }}"
                                data-depot="{{ strtolower($truck->homeDepot?->name ?? '') }}"
                                data-status="{{ $truck->operational_status }}">

                                {{-- Kendaraan --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-blue-50
                                                flex items-center justify-center flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001
                                                               1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1
                                                               0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0
                                                               01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2
                                                               2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm font-mono">
                                                {{ $truck->plate_number }}
                                            </div>
                                            <div class="text-xs text-slate-400 mt-0.5">
                                                {{ $truck->truck_type_label }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kondisi --}}
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5
                                             rounded-lg text-[10px] font-bold border {{ $statusBg }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                        {{ strtoupper($statusLabel) }}
                                    </span>
                                </td>

                                {{-- Muatan --}}
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-semibold text-slate-700">
                                        {{ number_format($truck->max_weight_kg, 0) }}
                                    </span>
                                    <span class="text-xs text-slate-400 ml-0.5">kg</span>
                                </td>

                                {{-- Depot Asal --}}
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs
                                             font-medium text-slate-600">
                                        <span class="w-2 h-2 rounded-sm bg-blue-400 flex-shrink-0"></span>
                                        {{ $truck->homeDepot?->name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Lokasi Kini --}}
                                <td class="px-6 py-4">
                                    <span
                                        class="font-mono text-xs text-slate-500
                                             bg-slate-100 px-2 py-1 rounded-md">
                                        {{ $truck->currentCity?->name ?? '—' }}
                                    </span>
                                </td>

                                {{-- Tombol --}}
                                <td class="px-6 py-4 text-center">
                                    <button type="button" onclick="bukaModalEdit({{ $truck->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5
                                           text-[11px] font-semibold rounded-lg border
                                           border-blue-200 text-blue-700 bg-blue-50
                                           hover:bg-blue-100 transition-all">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0
                                                           002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828
                                                           15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="1.25">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1
                                                           1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414
                                                           3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5
                                                           17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                        </svg>
                                        <p class="text-sm font-medium text-slate-500">
                                            Belum ada kendaraan terdaftar
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 bg-white">
                {{ $trucks->links() }}
            </div>
        </section>

    </div>

    {{-- Ubah Data --}}
    <div id="modal-edit-truk" class="fixed inset-0 z-50 items-center justify-center bg-black/40 backdrop-blur-sm"
        role="dialog" aria-modal="true" aria-labelledby="modal-edit-title">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-2xl mx-4 overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="h-4 w-4 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2
                                           2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h3 id="modal-edit-title" class="font-bold text-slate-800 text-sm">
                        Ubah
                        <span id="modal-edit-plat" class="font-mono text-blue-700"></span>
                    </h3>
                </div>
                <button type="button" onclick="tutupModalEdit()"
                    class="text-slate-400 hover:text-slate-700 transition-colors" aria-label="Tutup">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="form-edit-truk" method="POST" action="">
                @csrf @method('PUT')
                <div class="p-6 space-y-5">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                Nomor Plat
                            </label>
                            <input type="text" name="plate_number" id="edit_plate"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5
                                   text-sm font-mono text-slate-900 focus:bg-white focus:ring-2
                                   focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                Jenis Kendaraan
                            </label>
                            <select name="truck_type" id="edit_type"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5
                                   text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500
                                   focus:border-blue-500 outline-none transition-all">
                                @foreach ($presets as $key => $p)
                                    <option value="{{ $key }}">{{ $p['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                Kondisi Saat Ini
                            </label>
                            <select name="operational_status" id="edit_status"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5
                                   text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500
                                   focus:border-blue-500 outline-none transition-all">
                                <option value="available">Siap Jalan</option>
                                <option value="on_duty">Dalam Perjalanan</option>
                                <option value="maintenance">Di Bengkel</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3">
                            Spesifikasi Teknis
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1.5">Muatan Maks (kg)</label>
                                <input type="number" name="max_weight_kg" id="edit_maxkg" min="100"
                                    step="50"
                                    class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2
                                       text-sm text-slate-900 focus:ring-2 focus:ring-blue-500
                                       outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1.5">Solar (km/liter)</label>
                                <input type="number" name="fuel_efficiency_km_per_liter" id="edit_fuel" step="0.5"
                                    min="1"
                                    class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2
                                       text-sm text-slate-900 focus:ring-2 focus:ring-blue-500
                                       outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1.5">Panjang (cm)</label>
                                <input type="number" name="length_cm" id="edit_p" min="50"
                                    class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2
                                       text-sm text-slate-900 focus:ring-2 focus:ring-blue-500
                                       outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1.5">Lebar (cm)</label>
                                <input type="number" name="width_cm" id="edit_l" min="50"
                                    class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2
                                       text-sm text-slate-900 focus:ring-2 focus:ring-blue-500
                                       outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1.5">Tinggi (cm)</label>
                                <input type="number" name="height_cm" id="edit_t" min="50"
                                    class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2
                                       text-sm text-slate-900 focus:ring-2 focus:ring-blue-500
                                       outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-slate-50">
                    <button type="button" onclick="bukaModalHapusTruk()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold
                           rounded-xl border border-red-200 text-red-600 bg-red-50
                           hover:bg-red-100 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5
                                           7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus dari Daftar
                    </button>
                    <div class="flex gap-3">
                        <button type="button" onclick="tutupModalEdit()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600
                               bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2 text-sm font-bold text-white bg-blue-700
                               hover:bg-blue-800 rounded-xl transition-all flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Konfirmasi Hapus --}}
    <div id="modal-hapus-truk" class="fixed inset-0 z-[60] items-center justify-center bg-black/50 backdrop-blur-sm"
        role="dialog" aria-modal="true" aria-labelledby="modal-hapus-title">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-sm mx-4 p-6">
            <div class="flex items-start gap-4 mb-5">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5
                                       7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 id="modal-hapus-title" class="font-bold text-slate-900 text-sm">
                        Hapus kendaraan ini?
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Kendaraan
                        <span class="font-semibold font-mono text-slate-700" id="modal-hapus-plat"></span>
                        akan dihapus permanen beserta seluruh riwayat perjalanannya.
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="tutupModalHapusTruk()"
                    class="px-4 py-2 text-sm font-semibold text-slate-600
                       bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">
                    Batal
                </button>
                <form id="form-hapus-truk" method="POST" action="">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 text-sm font-bold text-white bg-red-600
                           hover:bg-red-700 rounded-xl transition-all flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5
                                           7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        @php
            $truckData = $trucks->map(
                fn($t) => [
                    'id' => $t->id,
                    'plate' => $t->plate_number,
                    'truck_type' => $t->truck_type,
                    'status' => $t->operational_status,
                    'max_kg' => (float) $t->max_weight_kg,
                    'fuel' => (float) $t->fuel_efficiency_km_per_liter,
                    'p' => (float) $t->length_cm,
                    'l' => (float) $t->width_cm,
                    't' => (float) $t->height_cm,
                ],
            );
        @endphp
        const TRUCK_DATA = @json($truckData);
        const PRESETS = @json($presets);

        let formOpen = true;

        function toggleForm() {
            formOpen = !formOpen;
            document.getElementById('form-body').classList.toggle('collapsed', !formOpen);
            document.getElementById('form-chevron').classList.toggle('collapsed', !formOpen);
        }

        function applyPreset(type) {
            const p = PRESETS[type];
            if (!p) return;
            document.getElementById('f_max_kg').value = p.max_kg;
            document.getElementById('f_fuel').value = p.fuel;
            document.getElementById('f_p').value = p.p;
            document.getElementById('f_l').value = p.l;
            document.getElementById('f_t').value = p.t;
        }
        const elType = document.getElementById('truck_type');
        if (elType) {
            elType.addEventListener('change', e => applyPreset(e.target.value));
            applyPreset(elType.value);
        }

        // Edit
        let currentTruckId = null;

        function bukaModalEdit(id) {
            const truk = TRUCK_DATA.find(t => t.id === id);
            if (!truk) return;
            currentTruckId = id;

            document.getElementById('modal-edit-plat').textContent = truk.plate;
            document.getElementById('edit_plate').value = truk.plate;
            document.getElementById('edit_type').value = truk.truck_type;
            document.getElementById('edit_status').value = truk.status;
            document.getElementById('edit_maxkg').value = truk.max_kg;
            document.getElementById('edit_fuel').value = truk.fuel;
            document.getElementById('edit_p').value = truk.p;
            document.getElementById('edit_l').value = truk.l;
            document.getElementById('edit_t').value = truk.t;
            document.getElementById('form-edit-truk').action = `/trucks/${id}`;
            document.getElementById('modal-hapus-plat').textContent = truk.plate;
            document.getElementById('modal-edit-truk').classList.add('aktif');
        }

        function tutupModalEdit() {
            document.getElementById('modal-edit-truk').classList.remove('aktif');
            currentTruckId = null;
        }

        // Hapus
        function bukaModalHapusTruk() {
            if (!currentTruckId) return;
            document.getElementById('form-hapus-truk').action = `/trucks/${currentTruckId}`;
            document.getElementById('modal-hapus-truk').classList.add('aktif');
        }

        function tutupModalHapusTruk() {
            document.getElementById('modal-hapus-truk').classList.remove('aktif');
        }

        document.getElementById('modal-edit-truk').addEventListener('click', function(e) {
            if (e.target === this) tutupModalEdit();
        });
        document.getElementById('modal-hapus-truk').addEventListener('click', function(e) {
            if (e.target === this) tutupModalHapusTruk();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            if (document.getElementById('modal-hapus-truk').classList.contains('aktif'))
                tutupModalHapusTruk();
            else
                tutupModalEdit();
        });

        // Filter + search tabel
        let filterAktif = 'all';

        function filterTabel(filter, btn) {
            filterAktif = filter;
            document.querySelectorAll('.tab-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            terapkanFilter();
        }

        document.getElementById('search-truk').addEventListener('input', terapkanFilter);

        function terapkanFilter() {
            const q = document.getElementById('search-truk').value.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('#tbody-truk tr[data-plat]').forEach(tr => {
                const cocokCari = !q || tr.dataset.plat.includes(q) || tr.dataset.depot.includes(q);
                const cocokStatus = filterAktif === 'all' || tr.dataset.status === filterAktif;

                if (cocokCari && cocokStatus) {
                    tr.style.display = '';
                    visible++;
                } else {
                    tr.style.display = 'none';
                }
            });

            const el = document.getElementById('jumlah-kendaraan');
            if (el) el.textContent = `Menampilkan ${visible} unit kendaraan.`;
        }
    </script>

@endsection
