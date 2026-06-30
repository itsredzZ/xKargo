@extends('layouts.app')
@section('title', 'Input Pesanan Harian')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 w-full font-sans">

    {{-- ─── HEADER ─────────────────────────────────────────────── --}}
    <header class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-700 flex items-center justify-center shadow-md shadow-teal-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Input Pesanan Harian</h1>
                <p class="text-sm text-slate-500">Tambahkan barang lalu lanjut ke Optimasi.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            {{-- Badge carry-over count jika ada --}}
            @if(isset($carryoverItems) && $carryoverItems->count() > 0)
            <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-amber-50 text-amber-700 border border-amber-300 rounded-xl px-3 py-2">
                ⚡ {{ $carryoverItems->count() }} carry-over dari kemarin
            </span>
            @endif
            <a href="{{ route('pso.results') }}"
               class="bg-violet-600 hover:bg-violet-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md flex items-center gap-2 transition-all">
                Lanjut Optimasi
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </header>

    {{-- ─── FLASH MESSAGES ───────────────────────────────────────
    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700 font-semibold">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700 font-semibold">
        ❌ {{ session('error') }}
    </div>
    @endif --}}

    {{-- Error baris Excel (jika ada baris yang gagal diimport) --}}
    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="mb-5 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800">
        <p class="font-bold mb-2">⚠️ Beberapa baris Excel gagal diimport:</p>
        <ul class="list-disc list-inside space-y-0.5 text-xs">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <p class="text-xs text-amber-600 mt-2">Pastikan nama Depot dan Kota Tujuan di kolom G & H sesuai dengan data di database.</p>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── PANEL KIRI: FORM TAMBAH PESANAN ──────────────── --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 h-fit">
            <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wider mb-4">Tambah Pesanan</h2>

            {{-- TAB SWITCH: MANUAL / EXCEL --}}
            <div class="flex bg-slate-100 rounded-lg p-1 mb-4">
                <button type="button" onclick="switchTab('manual')" id="tab-manual"
                    class="flex-1 text-xs font-bold py-2 rounded-md transition bg-white text-teal-700 shadow-sm">
                    Form Manual
                </button>
                <button type="button" onclick="switchTab('excel')" id="tab-excel"
                    class="flex-1 text-xs font-bold py-2 rounded-md transition text-slate-500">
                    Upload Excel
                </button>
            </div>

            {{-- ════════════════════════════════════════ --}}
            {{-- FORM MANUAL                             --}}
            {{-- ════════════════════════════════════════ --}}
            <form id="form-manual" method="POST" action="{{ route('pso.orders') }}" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="Nama Barang"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <input type="number" name="length_cm" placeholder="P (cm)" step="0.1" min="1" max="200"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                        <p class="text-[10px] text-slate-400 mt-1 text-center">maks 200</p>
                    </div>
                    <div>
                        <input type="number" name="width_cm" placeholder="L (cm)" step="0.1" min="1" max="130"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                        <p class="text-[10px] text-slate-400 mt-1 text-center">maks 130</p>
                    </div>
                    <div>
                        <input type="number" name="height_cm" placeholder="T (cm)" step="0.1" min="1" max="130"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                        <p class="text-[10px] text-slate-400 mt-1 text-center">maks 130</p>
                    </div>
                </div>
                <input type="number" step="0.1" min="0.1" name="weight_kg" placeholder="Berat (kg)"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                <select name="origin_depot_id"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                    <option value="">Pilih Depot Asal...</option>
                    @foreach($depots as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
                <select name="destination_city_id"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                    <option value="">Pilih Kota Tujuan...</option>
                    @foreach($allCities as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}{{ $c->is_depot ? ' (Depot)' : '' }}</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3 rounded-xl text-sm font-bold shadow-md transition-all">
                    Tambah ke Antrian
                </button>
            </form>

            {{-- ════════════════════════════════════════ --}}
            {{-- FORM UPLOAD EXCEL                       --}}
            {{-- ════════════════════════════════════════ --}}
            <form id="form-excel" method="POST" action="{{ route('pso.orders.import') }}"
                enctype="multipart/form-data" class="space-y-3 hidden">
                @csrf

                {{-- Panduan format kolom --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-700">
                    <p class="font-bold mb-2">📋 Format Kolom Excel (Baris 1 = Header):</p>
                    <table class="w-full text-left">
                        <tbody class="space-y-0.5">
                            <tr><th class="font-semibold pr-2 py-0.5 text-blue-600">A</th><td>Nama Barang</td></tr>
                            <tr><th class="font-semibold pr-2 py-0.5 text-blue-600">B</th><td>Berat (kg)</td></tr>
                            <tr><th class="font-semibold pr-2 py-0.5 text-blue-600">C</th><td>Panjang (cm)</td></tr>
                            <tr><th class="font-semibold pr-2 py-0.5 text-blue-600">D</th><td>Lebar (cm)</td></tr>
                            <tr><th class="font-semibold pr-2 py-0.5 text-blue-600">E</th><td>Tinggi (cm)</td></tr>
                            <tr><th class="font-semibold pr-2 py-0.5 text-blue-600">F</th><td>Jumlah</td></tr>
                            <tr class="bg-amber-50 rounded">
                                <th class="font-bold pr-2 py-0.5 text-amber-700">G</th>
                                <td class="font-semibold text-amber-700">Depot Asal (nama persis)</td>
                            </tr>
                            <tr class="bg-amber-50 rounded">
                                <th class="font-bold pr-2 py-0.5 text-amber-700">H</th>
                                <td class="font-semibold text-amber-700">Kota Tujuan (nama persis)</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="mt-2 text-blue-600">
                        💡 Kolom G & H harus menggunakan nama kota <b>persis seperti di database</b>
                        (misal: <b>Surabaya</b>, <b>Malang</b>, <b>Kediri</b>).
                        Tiap baris bisa punya depot dan tujuan berbeda.
                    </p>
                </div>

                {{-- Nama depot yang tersedia (untuk referensi) --}}
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-600">
                    <p class="font-bold mb-1 text-slate-700">Depot yang tersedia (kolom G):</p>
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @foreach($depots as $d)
                        <span class="bg-indigo-50 text-indigo-700 border border-indigo-200 px-2 py-0.5 rounded-md font-mono font-semibold">
                            {{ $d->name }}
                        </span>
                        @endforeach
                    </div>
                </div>

                {{-- File picker --}}
                <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center hover:border-teal-500 transition cursor-pointer relative">
                    <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" id="file-excel"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required
                        onchange="document.getElementById('file-name').innerText = this.files[0]?.name || 'Pilih file Excel'">
                    <p class="text-2xl mb-1">📄</p>
                    <p class="text-sm font-semibold text-slate-600" id="file-name">Klik untuk pilih file Excel</p>
                    <p class="text-xs text-slate-400 mt-1">.xlsx, .xls, atau .csv — Maks 2MB</p>
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl text-sm font-bold shadow-md transition-all">
                    Upload & Import Excel
                </button>
            </form>
        </section>

        {{-- ─── PANEL KANAN: TABEL ANTRIAN ────────────────────── --}}
        <section class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h2 class="font-bold text-slate-700 text-xs uppercase tracking-wider">Antrian Hari Ini</h2>
                <div class="flex items-center gap-2">
                    @php
                        $coCount  = isset($carryoverItems) ? $carryoverItems->count() : 0;
                        $newCount = $todayOrders->sum(fn($o) => $o->items->count());
                    @endphp
                    @if($coCount > 0)
                    <span class="text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-lg px-2.5 py-1">
                        ⚡ {{ $coCount }} CO
                    </span>
                    @endif
                    <span class="text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200 rounded-lg px-3 py-1">
                        {{ $newCount + $coCount }} Pesanan
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[560px]">
                    <thead class="text-slate-400 uppercase text-[10px] tracking-widest border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3 font-bold">Status</th>
                            <th class="px-5 py-3 font-bold">Nama Barang</th>
                            <th class="px-5 py-3 font-bold">Dimensi (PxLxT)</th>
                            <th class="px-5 py-3 font-bold">Berat</th>
                            <th class="px-5 py-3 font-bold">Rute</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">

                        {{-- ── CARRY-OVER ITEMS DULU (PRIORITAS) ─────────── --}}
                        @if(isset($carryoverItems) && $carryoverItems->count() > 0)
                            {{-- Separator header --}}
                            <tr>
                                <td colspan="5" class="px-5 py-2 bg-amber-50 border-y border-amber-200">
                                    <div class="flex items-center gap-2 text-xs font-bold text-amber-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        CARRY-OVER DARI KEMARIN — Akan masuk truk lebih dulu (prioritas PSO)
                                    </div>
                                </td>
                            </tr>
                            @foreach($carryoverItems as $item)
                            <tr class="bg-amber-50/40 hover:bg-amber-50 transition-colors border-l-4 border-amber-400">
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-300 px-2 py-1 rounded-md">
                                        ⚡ Carry-over
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm font-bold text-slate-800">
                                    {{ $item->name }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded-md">
                                        {{ $item->length_cm }}x{{ $item->width_cm }}x{{ $item->height_cm }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-slate-600">{{ $item->weight_kg }} kg</td>
                                <td class="px-5 py-3 text-sm">
                                    <span class="text-indigo-600 font-semibold">
                                        {{ $item->deliveryOrder->originDepot->name ?? '-' }}
                                    </span>
                                    <span class="text-slate-400 mx-1">→</span>
                                    <span class="text-teal-600 font-semibold">
                                        {{ $item->deliveryOrder->destinationCity->name ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach

                            {{-- Separator sebelum barang baru --}}
                            @if($todayOrders->count() > 0)
                            <tr>
                                <td colspan="5" class="px-5 py-2 bg-slate-50 border-y border-slate-200">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">
                                        Barang Baru Hari Ini
                                    </span>
                                </td>
                            </tr>
                            @endif
                        @endif

                        {{-- ── BARANG BARU HARI INI ───────────────────────── --}}
                        @forelse($todayOrders as $order)
                            @foreach($order->items as $item)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3">
                                    @if($item->is_carryover)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-300 px-2 py-1 rounded-md">
                                        ⚡ Carry-over
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-1 rounded-md">
                                        ✦ Baru
                                    </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-sm font-bold text-slate-800">{{ $item->name }}</td>
                                <td class="px-5 py-3">
                                    <span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded-md">
                                        {{ $item->length_cm }}x{{ $item->width_cm }}x{{ $item->height_cm }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-slate-600">{{ $item->weight_kg }} kg</td>
                                <td class="px-5 py-3 text-sm">
                                    <span class="text-indigo-600 font-semibold">{{ $order->originDepot->name ?? '-' }}</span>
                                    <span class="text-slate-400 mx-1">→</span>
                                    <span class="text-teal-600 font-semibold">{{ $order->destinationCity->name ?? '-' }}</span>
                                </td>
                            </tr>
                            @endforeach
                        @empty
                            @if(!isset($carryoverItems) || $carryoverItems->count() === 0)
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                    Belum ada pesanan. Tambah via form atau upload Excel.
                                </td>
                            </tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
function switchTab(tab) {
    const isManual = tab === 'manual';
    document.getElementById('form-manual').classList.toggle('hidden', !isManual);
    document.getElementById('form-excel').classList.toggle('hidden', isManual);

    const active   = 'bg-white text-teal-700 shadow-sm';
    const inactive = 'text-slate-500';
    const tabManual = document.getElementById('tab-manual');
    const tabExcel  = document.getElementById('tab-excel');

    if (isManual) {
        tabManual.className = `flex-1 text-xs font-bold py-2 rounded-md transition ${active}`;
        tabExcel.className  = `flex-1 text-xs font-bold py-2 rounded-md transition ${inactive}`;
    } else {
        tabExcel.className  = `flex-1 text-xs font-bold py-2 rounded-md transition ${active}`;
        tabManual.className = `flex-1 text-xs font-bold py-2 rounded-md transition ${inactive}`;
    }
}
</script>
@endsection