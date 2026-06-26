{{-- resources/views/settings/index.blade.php --}}

@extends('layouts.app')
@section('title', 'Parameter PSO')

@section('content')
<div class="space-y-6 max-w-3xl">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-800">⚙️ Parameter PSO & Operasional</h1>
        <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">
            Berlaku pada run PSO berikutnya
        </span>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
        @csrf @method('PUT')

        {{-- PSO Parameters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-medium text-gray-700 mb-4 flex items-center gap-2">
                ⚡ Parameter Algoritma PSO
                <span class="text-xs text-gray-400 font-normal">(sinkron dengan pso_no2_last_boss.py)</span>
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $psoFields = [
                        'n_partikel'      => ['label' => 'Jumlah Partikel', 'min' => 5, 'max' => 500, 'step' => 1,    'hint' => 'Rekomendasi: 20–50'],
                        'n_iterasi'       => ['label' => 'Iterasi Maks',    'min' => 10,'max' => 1000,'step' => 10,   'hint' => 'Rekomendasi: 50–200'],
                        'early_stop_iter' => ['label' => 'Early Stop',      'min' => 5, 'max' => 200, 'step' => 1,    'hint' => 'Hentikan jika tidak ada perbaikan'],
                        'base_seed'       => ['label' => 'Random Seed',     'min' => 0, 'max' => 9999,'step' => 1,    'hint' => '42 = hasil reprodusibel'],
                        'w_max'           => ['label' => 'W Max (inersia)',  'min' => 0.5,'max' => 1.0,'step' => 0.05, 'hint' => 'Default: 0.9'],
                        'w_min'           => ['label' => 'W Min (inersia)',  'min' => 0.1,'max' => 0.9,'step' => 0.05, 'hint' => 'Default: 0.4'],
                        'c1'              => ['label' => 'C1 (kognitif)',    'min' => 0.5,'max' => 4.0,'step' => 0.1,  'hint' => 'Default: 2.0'],
                        'c2'              => ['label' => 'C2 (sosial)',      'min' => 0.5,'max' => 4.0,'step' => 0.1,  'hint' => 'Default: 2.0'],
                    ];
                @endphp

                @foreach ($psoFields as $key => $field)
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ $field['label'] }}</label>
                    <input type="number"
                           name="{{ $key }}"
                           value="{{ old($key, $pso[$key] ?? '') }}"
                           min="{{ $field['min'] }}"
                           max="{{ $field['max'] }}"
                           step="{{ $field['step'] }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">{{ $field['hint'] }}</p>
                    @error($key) <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                </div>
                @endforeach
            </div>
        </div>

        {{-- Operasional Parameters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-medium text-gray-700 mb-4">💰 Parameter Operasional</h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $opsFields = [
                        'harga_solar'  => ['label' => 'Harga Solar (Rp/L)', 'min' => 1000, 'max' => 20000, 'step' => 100,  'hint' => 'Harga BBM per liter saat ini'],
                        'tarif_dasar'  => ['label' => 'Tarif Dasar',        'min' => 1,    'max' => 1000,  'step' => 1,    'hint' => 'Rp per (kg·km)'],
                        'bbm_base'     => ['label' => 'BBM Base (L/km)',     'min' => 0.01, 'max' => 1.0,   'step' => 0.01, 'hint' => 'Konsumsi dasar truk kosong'],
                        'bbm_faktor'   => ['label' => 'BBM Faktor',         'min' => 0.001,'max' => 0.5,   'step' => 0.001,'hint' => 'Tambahan L/km per 1000 kg'],
                    ];
                @endphp

                @foreach ($opsFields as $key => $field)
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ $field['label'] }}</label>
                    <input type="number"
                           name="{{ $key }}"
                           value="{{ old($key, $operasional[$key] ?? '') }}"
                           min="{{ $field['min'] }}"
                           max="{{ $field['max'] }}"
                           step="{{ $field['step'] }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">{{ $field['hint'] }}</p>
                    @error($key) <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                </div>
                @endforeach
            </div>
        </div>

        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
            💾 Simpan Semua Parameter
        </button>
    </form>

    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
        <p class="font-medium mb-1">⚡ Cara kerja sinkronisasi</p>
        <p class="text-amber-700">
            Parameter disimpan ke <code class="bg-amber-100 px-1 rounded">db_xkargo.settings</code>.
            PSO engine Streamlit membaca tabel ini setiap kali run dimulai —
            perubahan di sini berlaku pada run berikutnya tanpa perlu restart Streamlit.
        </p>
    </div>

</div>
@endsection
