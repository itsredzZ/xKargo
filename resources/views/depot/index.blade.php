@extends('layouts.app')
@section('title', 'Lokasi Depot')

@section('content')

    <style>
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

        #modal-edit-depot {
            display: none;
        }

        #modal-edit-depot.aktif {
            display: flex;
        }

        /* Slot parkir */
        .slot-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
        }

        .slot-item {
            width: 36px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid transparent;
        }

        .slot-avail {
            background: #D1FAE5;
            border-color: #6EE7B7;
        }

        .slot-duty {
            background: #FEF3C7;
            border-color: #FCD34D;
        }

        .slot-maint {
            background: #FEE2E2;
            border-color: #FCA5A5;
        }

        .slot-empty {
            background: #F1F5F9;
            border-color: #CBD5E1;
            border-style: dashed;
        }

        .depot-card {
            transition: box-shadow 0.15s;
        }

        .depot-card:hover {
            box-shadow: 0 4px 20px rgba(15, 23, 42, .08);
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 py-8 w-full font-sans">

        {{-- Header --}}
        <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-blue-700 flex items-center justify-center shadow-md shadow-blue-100 flex-shrink-0">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 9.5L12 4l9 5.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 21V12h6v9" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Lokasi Depot</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Status armada dan kapasitas per titik keberangkatan</p>
                </div>
            </div>
            <span
                class="inline-flex items-center gap-1.5 text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200 rounded-lg px-3 py-1.5 self-start sm:self-auto">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ $depots->count() }} lokasi terdaftar
            </span>
        </header>

        @if ($depots->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.25">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 9.5L12 4l9 5.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 21V12h6v9" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-700 mb-1">Belum ada lokasi depot</h3>
                <p class="text-sm text-slate-400 max-w-sm">
                    Tandai sebuah kota sebagai depot keberangkatan di halaman
                    <a href="{{ route('cities.index') }}" class="text-blue-600 font-semibold hover:underline">Kota &amp;
                        Jaringan</a>.
                </p>
            </div>
        @else
            @if ($totalAvail === 0 && $totalTrucks > 0)
                <div
                    class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 text-sm font-medium px-5 py-4 rounded-xl">
                    <svg class="h-5 w-5 flex-shrink-0 mt-0.5 text-red-500" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <strong class="font-bold">Tidak ada truk yang siap jalan.</strong>
                        Pengiriman hari ini tidak dapat dijadwalkan. Ubah kondisi salah satu truk
                        melalui halaman <a href="{{ route('trucks.index') }}" class="underline font-bold">Armada Truk</a>.
                    </div>
                </div>
            @endif

            {{-- Kartu depot --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
                @foreach ($depots as $depot)
                    @php
                        $trucksHere = $trucksByDepot->get($depot->id, collect());
                        $nHere = $trucksHere->count();
                        $nAvail = $trucksHere->where('operational_status', 'available')->count();
                        $nDuty = $trucksHere->where('operational_status', 'on_duty')->count();
                        $nMaint = $trucksHere->where('operational_status', 'maintenance')->count();
                        $maxCap = $depot->max_truck_capacity;
                        $pct = $maxCap ? $nHere / $maxCap : null;

                        $borderColor = !$depot->is_active
                            ? 'border-slate-200'
                            : ($pct !== null && $pct >= 1.0
                                ? 'border-red-300'
                                : ($pct !== null && $pct >= 0.8
                                    ? 'border-amber-300'
                                    : 'border-slate-200'));

                        $availPlates = $trucksHere->where('operational_status', 'available')->pluck('plate_number');

                        // Susun truk: available dulu, on_duty, maintenance
                        $sortedTrucks = $trucksHere
                            ->sortBy(
                                fn($t) => match ($t->operational_status) {
                                    'available' => 0,
                                    'on_duty' => 1,
                                    'maintenance' => 2,
                                    default => 3,
                                },
                            )
                            ->values();
                    @endphp

                    <div
                        class="depot-card bg-white rounded-2xl border shadow-sm flex flex-col transition-all {{ $depot->is_active ? 'border-slate-200' : 'border-slate-100 bg-slate-50' }}">

                        <div class="{{ $depot->is_active ? '' : 'grayscale opacity-50 pointer-events-none' }}">
                            {{-- Header kartu --}}
                            <div class="px-5 pt-5 pb-4 border-b border-slate-100">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 9.5L12 4l9 5.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 21V12h6v9" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h2 class="font-extrabold text-slate-900 text-base leading-tight">
                                                {{ $depot->name }}</h2>
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold border flex-shrink-0 {{ $depot->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $depot->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $depot->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Slot parkir visual --}}
                            <div class="px-5 py-4 border-b border-slate-100">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Slot Parkir
                                    </p>
                                    @if ($maxCap)
                                        <span
                                            class="text-xs font-semibold {{ $pct >= 1.0 ? 'text-red-600' : ($pct >= 0.8 ? 'text-amber-600' : 'text-slate-600') }}">
                                            {{ $nHere }} / {{ $maxCap }} terisi
                                        </span>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Kapasitas belum diatur</span>
                                    @endif
                                </div>

                                @if ($maxCap)
                                    <div class="slot-grid">
                                        @for ($s = 0; $s < $maxCap; $s++)
                                            @php $t = $sortedTrucks->get($s); @endphp
                                            @if ($t)
                                                @php
                                                    $sc = match ($t->operational_status) {
                                                        'available' => 'slot-avail',
                                                        'on_duty' => 'slot-duty',
                                                        'maintenance' => 'slot-maint',
                                                        default => 'slot-avail',
                                                    };
                                                    $tip =
                                                        $t->plate_number .
                                                        ' — ' .
                                                        match ($t->operational_status) {
                                                            'available' => 'Siap Jalan',
                                                            'on_duty' => 'Dalam Perjalanan',
                                                            'maintenance' => 'Di Bengkel',
                                                            default => '',
                                                        };
                                                @endphp
                                                <div class="slot-item {{ $sc }}" title="{{ $tip }}">
                                                    <svg width="20" height="13" viewBox="0 0 22 14" fill="none">
                                                        <rect x="1" y="2" width="13" height="8" rx="1.5"
                                                            fill="{{ match ($t->operational_status) {'available' => '#059669','on_duty' => '#D97706','maintenance' => '#DC2626',default => '#64748B'} }}"
                                                            opacity=".75" />
                                                        <path d="M14 5h3.5l2 3.5v2H14V5z"
                                                            fill="{{ match ($t->operational_status) {'available' => '#059669','on_duty' => '#D97706','maintenance' => '#DC2626',default => '#64748B'} }}"
                                                            opacity=".75" />
                                                        <circle cx="5" cy="12" r="1.6" fill="#334155" />
                                                        <circle cx="12" cy="12" r="1.6" fill="#334155" />
                                                        <circle cx="17.5" cy="12" r="1.6" fill="#334155" />
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="slot-item slot-empty" title="Slot kosong"></div>
                                            @endif
                                        @endfor
                                    </div>

                                    @if ($pct >= 1.0)
                                        <p class="mt-2 text-[11px] font-semibold text-red-600 flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Gudang penuh — truk baru tidak bisa ditempatkan di sini
                                        </p>
                                    @elseif ($pct >= 0.8)
                                        <p class="mt-2 text-[11px] font-semibold text-amber-600 flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Hampir penuh ({{ round($pct * 100) }}%) — pertimbangkan relokasi
                                        </p>
                                    @endif
                                @else
                                    <div
                                        class="mt-1 bg-slate-50 border border-dashed border-slate-200 rounded-lg px-3 py-2.5 text-[11px] text-slate-400">
                                        Atur jumlah slot parkir via tombol <strong class="text-slate-500">Atur
                                            Kapasitas</strong> di bawah.
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100">
                                <div class="px-4 py-3 text-center">
                                    <p class="text-xl font-black text-emerald-600">{{ $nAvail }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-0.5">Siap
                                        Jalan</p>
                                </div>
                                <div class="px-4 py-3 text-center">
                                    <p class="text-xl font-black text-amber-500">{{ $nDuty }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-0.5">Di Jalan
                                    </p>
                                </div>
                                <div class="px-4 py-3 text-center">
                                    <p class="text-xl font-black text-red-500">{{ $nMaint }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-0.5">Bengkel
                                    </p>
                                </div>
                            </div>

                            {{-- Plat siap jalan --}}
                            @if ($availPlates->isNotEmpty())
                                <div class="px-5 py-3 bg-emerald-50 border-b border-emerald-100">
                                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide mb-1.5">Siap
                                        berangkat hari ini</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($availPlates as $plate)
                                            <span
                                                class="font-mono text-[11px] font-bold bg-white text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-md">
                                                {{ $plate }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif ($depot->is_active)
                                <div class="px-5 py-3 bg-red-50 border-b border-red-100">
                                    <p class="text-[11px] font-semibold text-red-600 flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        Tidak ada truk yang siap berangkat hari ini
                                    </p>
                                </div>
                            @endif

                        </div>

                        {{-- Footer --}}
                        <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                            <button type="button"
                                @if ($depot->is_active) onclick="bukaModalEdit({{ $depot->id }}, '{{ $depot->name }}', {{ $depot->max_truck_capacity ?? 4 }}, {{ $depot->max_warehouse_kg ?? 20000 }})"
        @else
            disabled @endif
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold rounded-lg border transition-all 
        {{ $depot->is_active
            ? 'border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100'
            : 'border-slate-200 text-slate-400 bg-slate-50 cursor-not-allowed' }}">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Atur Kapasitas
                            </button>

                            <form method="POST" action="{{ route('depot.update') }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="depot_id" value="{{ $depot->id }}">
                                <input type="hidden" name="is_active" value="{{ $depot->is_active ? 0 : 1 }}">
                                @if ($depot->max_truck_capacity)
                                    <input type="hidden" name="max_truck_capacity"
                                        value="{{ $depot->max_truck_capacity }}">
                                @endif
                                @if ($depot->max_warehouse_kg)
                                    <input type="hidden" name="max_warehouse_kg"
                                        value="{{ $depot->max_warehouse_kg }}">
                                @endif
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold rounded-lg border transition-all
        {{ $depot->is_active
            ? 'border-red-200 text-red-600 bg-red-50 hover:bg-red-100'
            : 'border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                                    @if ($depot->is_active)
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        Nonaktifkan
                                    @else
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Aktifkan Kembali
                                    @endif
                                </button>
                            </form>
                        </div>

                        {{-- Info kapasitas gudang --}}
                        @if ($depot->max_warehouse_kg)
                            <div class="px-5 pb-3.5 -mt-1">
                                <p class="text-[11px] {{ $depot->is_active ? 'text-slate-400' : 'text-slate-300' }}">
                                    Batas muatan gudang:
                                    <span
                                        class="font-semibold font-mono {{ $depot->is_active ? 'text-slate-600' : 'text-slate-400' }}">
                                        {{ number_format($depot->max_warehouse_kg, 0) }} kg
                                    </span>
                                </p>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>

        @endif
    </div>

    <div id="modal-edit-depot" class="fixed inset-0 z-[60] items-center justify-center bg-black/50 backdrop-blur-sm"
        role="dialog" aria-modal="true" aria-labelledby="modal-depot-title">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md mx-4 overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 9.5L12 4l9 5.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 21V12h6v9" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="modal-depot-title" class="font-bold text-slate-900 text-sm">Atur Kapasitas Gudang</h3>
                        <p id="modal-depot-nama" class="text-xs text-slate-400 mt-0.5"></p>
                    </div>
                </div>
                <button type="button" onclick="tutupModalEdit()"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-all">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form id="form-edit-depot" method="POST" action="{{ route('depot.update') }}">
                @csrf @method('PATCH')
                <input type="hidden" name="depot_id" id="modal-depot-id">
                <input type="hidden" name="is_active" id="modal-depot-aktif">

                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                            Jumlah slot parkir truk
                        </label>
                        <input type="number" name="max_truck_capacity" id="modal-max-truk" min="1"
                            max="99" placeholder="Contoh: 4"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        <p class="text-[11px] text-slate-400 mt-1.5">
                            Berapa banyak truk yang bisa parkir di gudang ini secara bersamaan.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                            Batas muatan gudang (kg)
                        </label>
                        <input type="number" name="max_warehouse_kg" id="modal-max-wh" min="1000" step="1000"
                            placeholder="Contoh: 20000"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        <p class="text-[11px] text-slate-400 mt-1.5">
                            Total berat barang maksimal yang bisa disimpan di gudang sebelum pengiriman.
                        </p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50">
                    <button type="button" onclick="tutupModalEdit()"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-sm font-bold text-white bg-blue-700 hover:bg-blue-800 rounded-xl transition-all flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        function bukaModalEdit(id, nama, maxTruk, maxWh) {
            document.getElementById('modal-depot-id').value = id;
            document.getElementById('modal-depot-aktif').value = 1;
            document.getElementById('modal-depot-nama').textContent = nama;
            document.getElementById('modal-max-truk').value = maxTruk;
            document.getElementById('modal-max-wh').value = maxWh;
            document.getElementById('modal-edit-depot').classList.add('aktif');
        }

        function tutupModalEdit() {
            document.getElementById('modal-edit-depot').classList.remove('aktif');
        }

        document.getElementById('modal-edit-depot').addEventListener('click', function(e) {
            if (e.target === this) tutupModalEdit();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') tutupModalEdit();
        });
    </script>

@endsection
