@extends('layouts.app')
@section('title', 'Input Pesanan Harian')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 w-full font-sans">
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
        <a href="{{ route('pso.results') }}" class="bg-violet-600 hover:bg-violet-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md flex items-center gap-2">Lanjut Optimasi <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg></a>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 h-fit">
            <h2 class="font-semibold text-slate-700 text-sm uppercase tracking-wider mb-4">Tambah Barang</h2>
            @if(session('success')) <div class="mb-4 bg-green-50 text-green-700 p-3 rounded-xl text-sm font-semibold">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="mb-4 bg-red-50 text-red-700 p-3 rounded-xl text-sm font-semibold">{{ session('error') }}</div> @endif
            
            {{-- TAB SWITCH MANUAL / EXCEL --}}
            <div class="flex bg-slate-100 rounded-lg p-1 mb-4">
                <button type="button" onclick="switchTab('manual')" id="tab-manual" class="flex-1 text-xs font-bold py-2 rounded-md transition bg-white text-teal-700 shadow-sm">Form Manual</button>
                <button type="button" onclick="switchTab('excel')" id="tab-excel" class="flex-1 text-xs font-bold py-2 rounded-md transition text-slate-500">Upload Excel</button>
            </div>

            {{-- ========================================== --}}
            {{-- FORM MANUAL --}}
            {{-- ========================================== --}}
            <form id="form-manual" method="POST" action="{{ route('pso.orders') }}" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="Nama Barang" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                <div class="grid grid-cols-3 gap-2">
                    <input type="number" name="length_cm" placeholder="P (cm)" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                    <input type="number" name="width_cm" placeholder="L (cm)" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                    <input type="number" name="height_cm" placeholder="T (cm)" class="bg-slate-50 border border-slate-300 rounded-xl px-3 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                </div>
                <input type="number" step="0.1" name="weight_kg" placeholder="Berat (kg)" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                <select name="origin_depot_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                    <option value="">Pilih Depot Asal...</option>
                    @foreach($depots as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
                <select name="destination_city_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                    <option value="">Pilih Kota Tujuan...</option>
                    @foreach($allCities as $c)<option value="{{ $c->id }}">{{ $c->name }} {{ $c->is_depot ? '(Depot)' : '' }}</option>@endforeach
                </select>
                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-700 text-white py-3 rounded-xl text-sm font-bold shadow-md">Tambah ke Antrian</button>
            </form>

            {{-- ========================================== --}}
            {{-- FORM UPLOAD EXCEL --}}
            {{-- ========================================== --}}
            <form id="form-excel" method="POST" action="{{ route('pso.orders.import') }}" enctype="multipart/form-data" class="space-y-3 hidden">
                @csrf
                
                {{-- Info Format --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-700">
                    <p class="font-bold mb-1">📋 Format Kolom Excel (Baris 1 = Header):</p>
                    <table class="w-full text-left mt-1">
                        <tr><th class="font-semibold pr-2">A:</th><td>Nama Barang</td></tr>
                        <tr><th class="font-semibold pr-2">B:</th><td>Berat (kg)</td></tr>
                        <tr><th class="font-semibold pr-2">C:</th><td>Panjang (cm)</td></tr>
                        <tr><th class="font-semibold pr-2">D:</th><td>Lebar (cm)</td></tr>
                        <tr><th class="font-semibold pr-2">E:</th><td>Tinggi (cm)</td></tr>
                        <tr><th class="font-semibold pr-2">F:</th><td>Jumlah</td></tr>
                    </table>
                </div>

                <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center hover:border-teal-500 transition cursor-pointer relative">
                    <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" id="file-excel" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required onchange="document.getElementById('file-name').innerText = this.files[0].name">
                    <p class="text-2xl mb-1">📄</p>
                    <p class="text-sm font-semibold text-slate-600" id="file-name">Klik untuk pilih file Excel</p>
                    <p class="text-xs text-slate-400 mt-1">.xlsx, .xls, atau .csv (Maks 2MB)</p>
                </div>

                <select name="origin_depot_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                    <option value="">Pilih Depot Asal (Untuk Semua Barang)...</option>
                    @foreach($depots as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
                <select name="destination_city_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-teal-500" required>
                    <option value="">Pilih Kota Tujuan (Untuk Semua Barang)...</option>
                    @foreach($allCities as $c)<option value="{{ $c->id }}">{{ $c->name }} {{ $c->is_depot ? '(Depot)' : '' }}</option>@endforeach
                </select>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl text-sm font-bold shadow-md">Upload & Import Excel</button>
            </form>
        </section>

        <section class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h2 class="font-bold text-slate-700 text-xs uppercase tracking-wider">Antrian Hari Ini</h2>
                <span class="text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200 rounded-lg px-3 py-1">{{ $todayOrders->count() }} Pesanan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[500px]">
                    <thead class="text-slate-400 uppercase text-[10px] tracking-widest border-b border-slate-200">
                        <tr><th class="px-6 py-3">Nama</th><th class="px-6 py-3">Dimensi (PxLxT)</th><th class="px-6 py-3">Berat</th><th class="px-6 py-3">Rute</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($todayOrders as $order)
                        @foreach($order->items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 text-sm font-bold text-slate-800">{{ $item->name }}</td>
                            <td class="px-6 py-3 text-xs font-mono text-slate-500 bg-slate-100 px-2 py-1 rounded-md inline-block mx-6">{{ $item->length_cm }}x{{ $item->width_cm }}x{{ $item->height_cm }}</td>
                            <td class="px-6 py-3 text-sm">{{ $item->weight_kg }} kg</td>
                            <td class="px-6 py-3 text-sm"><span class="text-indigo-600 font-semibold">{{ $order->originDepot->name ?? '-' }}</span> <span class="text-slate-400">→</span> <span class="text-teal-600 font-semibold">{{ $order->destinationCity->name ?? '-' }}</span></td>
                        </tr>
                        @endforeach
                        @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">Belum ada pesanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

{{-- Script kecil untuk switch tab --}}
<script>
function switchTab(tab) {
    const formManual = document.getElementById('form-manual');
    const formExcel = document.getElementById('form-excel');
    const tabManual = document.getElementById('tab-manual');
    const tabExcel = document.getElementById('tab-excel');

    if (tab === 'manual') {
        formManual.classList.remove('hidden');
        formExcel.classList.add('hidden');
        tabManual.classList.add('bg-white', 'text-teal-700', 'shadow-sm');
        tabManual.classList.remove('text-slate-500');
        tabExcel.classList.remove('bg-white', 'text-teal-700', 'shadow-sm');
        tabExcel.classList.add('text-slate-500');
    } else {
        formExcel.classList.remove('hidden');
        formManual.classList.add('hidden');
        tabExcel.classList.add('bg-white', 'text-teal-700', 'shadow-sm');
        tabExcel.classList.remove('text-slate-500');
        tabManual.classList.remove('bg-white', 'text-teal-700', 'shadow-sm');
        tabManual.classList.add('text-slate-500');
    }
}
</script>
@endsection