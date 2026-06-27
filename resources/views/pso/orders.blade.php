@extends('layouts.app')
@section('title', 'Input Pesanan Harian')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-8 w-full font-sans">
    <header class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-teal-600 flex items-center justify-center shadow-md shadow-teal-100">
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
            
            <form method="POST" action="{{ route('pso.orders') }}" class="space-y-3">
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
                <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-3 rounded-xl text-sm font-bold shadow-md">Tambah ke Antrian</button>
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
                            <td class="px-6 py-3 text-sm"><span class="text-indigo-600 font-semibold">{{ $order->originDepot->name }}</span> <span class="text-slate-400">→</span> <span class="text-teal-600 font-semibold">{{ $order->destinationCity->name }}</span></td>
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
@endsection