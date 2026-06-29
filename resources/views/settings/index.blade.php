{{-- resources/views/settings/index.blade.php --}}

@extends('layouts.app')
@section('title', 'Pengaturan')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 w-full font-sans space-y-6">

    {{-- ── HEADER ──────────────────────────────────────────────────── --}}
    <header class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-blue-700 flex items-center justify-center shadow-md flex-shrink-0">
                {{-- Gear / settings icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0
                             002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0
                             001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0
                             00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0
                             00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0
                             00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0
                             00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0
                             001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07
                             2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                    Pengaturan Optimasi
                </h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    Parameter PSO dan operasional yang digunakan mesin optimasi harian
                </p>
            </div>
        </div>
    </header>

    {{-- ── FLASH MESSAGE ───────────────────────────────────────────── --}}

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- ── PARAMETER PSO ───────────────────────────────────────── --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <header class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                {{-- Lightning / bolt icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wider">
                    Parameter Algoritma PSO
                </h2>
            </header>

            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    @php
                        $psoFields = [
                            'n_partikel'      => ['label' => 'Jumlah Partikel', 'min' => 5,   'max' => 500,  'step' => 1,    'hint' => 'Rekomendasi: 20–50'],
                            'n_iterasi'       => ['label' => 'Iterasi Maks',    'min' => 10,  'max' => 1000, 'step' => 10,   'hint' => 'Rekomendasi: 50–200'],
                            'early_stop_iter' => ['label' => 'Early Stop',      'min' => 5,   'max' => 200,  'step' => 1,    'hint' => 'Hentikan jika tidak ada perbaikan'],
                            'base_seed'       => ['label' => 'Random Seed',     'min' => 0,   'max' => 9999, 'step' => 1,    'hint' => '42 = hasil reprodusibel'],
                            'w_max'           => ['label' => 'W Max (inersia)', 'min' => 0.5, 'max' => 1.0,  'step' => 0.05, 'hint' => 'Default: 0.9'],
                            'w_min'           => ['label' => 'W Min (inersia)', 'min' => 0.1, 'max' => 0.9,  'step' => 0.05, 'hint' => 'Default: 0.4'],
                            'c1'              => ['label' => 'C1 (kognitif)',   'min' => 0.5, 'max' => 4.0,  'step' => 0.1,  'hint' => 'Default: 2.0'],
                            'c2'              => ['label' => 'C2 (sosial)',     'min' => 0.5, 'max' => 4.0,  'step' => 0.1,  'hint' => 'Default: 2.0'],
                        ];
                    @endphp

                    @foreach ($psoFields as $key => $field)
                    <div>
                        <label for="{{ $key }}"
                               class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                            {{ $field['label'] }}
                        </label>
                        <input type="number"
                               id="{{ $key }}"
                               name="{{ $key }}"
                               value="{{ old($key, $pso[$key] ?? '') }}"
                               min="{{ $field['min'] }}"
                               max="{{ $field['max'] }}"
                               step="{{ $field['step'] }}"
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5
                                      text-sm text-slate-900 focus:bg-white focus:ring-2
                                      focus:ring-indigo-500 focus:border-indigo-500
                                      outline-none transition-all
                                      @error($key) border-red-400 bg-red-50 @enderror">
                        <p class="text-xs text-slate-400 mt-1.5">{{ $field['hint'] }}</p>
                        @error($key)
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ── PARAMETER OPERASIONAL ───────────────────────────────── --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <header class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                {{-- Currency / money icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343
                             2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11
                             0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wider">
                    Parameter Operasional
                </h2>
            </header>

            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    @php
                        $opsFields = [
                            'harga_solar' => [
                                'label'   => 'Harga Solar (Rp/L)',
                                'min'     => 1000, 'max' => 20000, 'step' => 100,
                                'hint'    => 'Solar subsidi B35 saat ini. Default: Rp 6.800',
                                'default' => 6800,
                            ],
                            'tarif_dasar' => [
                                'label'   => 'Tarif Kirim (Rp/kg·km)',
                                'min'     => 1, 'max' => 1000, 'step' => 1,
                                'hint'    => 'Ongkos kirim per 1 kg per 1 km jarak tempuh. Default: Rp 15',
                                'default' => 15,
                            ],
                            'bbm_base'    => [
                                'label'   => 'Konsumsi Solar Truk Kosong (L/km)',
                                'min'     => 0.01, 'max' => 1.0, 'step' => 0.01,
                                'hint'    => 'Solar yang dipakai per km saat truk tidak bermuatan. Default: 0.12 (≈ 8 km/L)',
                                'default' => 0.12,
                            ],
                            'bbm_faktor'  => [
                                'label'   => 'Faktor Beban Solar (L/km per ton)',
                                'min'     => 0.001, 'max' => 0.5, 'step' => 0.001,
                                'hint'    => 'Tambahan solar per km untuk setiap 1.000 kg muatan. Default: 0.02',
                                'default' => 0.02,
                            ],
                        ];
                    @endphp

                    @foreach ($opsFields as $key => $field)
                    <div>
                        <label for="{{ $key }}"
                               class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                            {{ $field['label'] }}
                        </label>
                        <input type="number"
                               id="{{ $key }}"
                               name="{{ $key }}"
                               value="{{ old($key, $operasional[$key] ?? $field['default']) }}"
                               min="{{ $field['min'] }}"
                               max="{{ $field['max'] }}"
                               step="{{ $field['step'] }}"
                               class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5
                                      text-sm text-slate-900 focus:bg-white focus:ring-2
                                      focus:ring-indigo-500 focus:border-indigo-500
                                      outline-none transition-all
                                      @error($key) border-red-400 bg-red-50 @enderror">
                        <p class="text-xs text-slate-400 mt-1.5">{{ $field['hint'] }}</p>
                        @error($key)
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ── TOMBOL SIMPAN ───────────────────────────────────────── --}}
        <div class="flex items-center gap-4">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white
                           px-6 py-3 rounded-xl text-sm font-bold shadow-md shadow-indigo-100
                           transition-all flex items-center gap-2">
                {{-- Floppy disk / save icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2
                             2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Simpan Semua Parameter
            </button>
            <p class="text-xs text-slate-400">
                Perubahan berlaku pada sesi optimasi berikutnya.
            </p>
        </div>

    </form>
</div>
@endsection
