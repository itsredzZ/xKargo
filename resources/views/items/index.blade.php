@extends('layouts.app')
@section('title', 'Database Barang')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">📦 Database Barang</h1>
            <p class="text-gray-500 text-sm mt-0.5">Data barang otomatis terisi dari halaman Input Pengiriman</p>
        </div>
        <a href="{{ route('pso.orders') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
            📝 Buka Input Pengiriman
        </a>
    </div>

    {{-- ============================================ --}}
    {{-- FILTER SECTION --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('items.index') }}" class="flex flex-wrap items-end gap-4">
            
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="filter_status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('filter_status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="terkirim" {{ request('filter_status') == 'terkirim' ? 'selected' : '' }}>Terkirim</option>
                    <option value="carryover" {{ request('filter_status') == 'carryover' ? 'selected' : '' }}>Carry-over</option>
                </select>
            </div>

            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Depot Asal</label>
                <select name="filter_depot" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Depot</option>
                    @foreach($depots as $id => $name)
                        <option value="{{ $id }}" {{ request('filter_depot') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Pesanan</label>
                <input type="date" name="filter_date" value="{{ request('filter_date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Filter
                </button>
                <a href="{{ route('items.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ============================================ --}}
    {{-- TABEL DATA BARANG --}}
    {{-- ============================================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        @if($items->isEmpty())
            <div class="p-10 text-center text-gray-400">
                <p class="text-3xl mb-2">📭</p>
                <p class="font-medium">Belum ada data barang.</p>
                <p class="text-sm mt-1">Data akan muncul setelah ada pesanan dari halaman Input Pengiriman.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 font-medium">Order ID</th>
                            <th class="px-4 py-3 font-medium">Nama Barang</th>
                            <th class="px-4 py-3 font-medium text-center">Dimensi (P×L×T)</th>
                            <th class="px-4 py-3 font-medium text-right">Berat (kg)</th>
                            <th class="px-4 py-3 font-medium text-center">Status</th>
                            <th class="px-4 py-3 font-medium">Asal → Tujuan</th>
                            <th class="px-4 py-3 font-medium">Tgl Pesan</th>
                            <th class="px-4 py-3 font-medium text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($items as $item)
                        <tr class="hover:bg-gray-50 transition">
                            
                            {{-- KOLOM ORDER ID (WAJIB) --}}
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                #{{ $item->order_id }}
                            </td>
                            
                            {{-- NAMA BARANG --}}
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $item->name }}
                            </td>
                            
                            {{-- DIMENSI --}}
                            <td class="px-4 py-3 text-center text-gray-600 whitespace-nowrap">
                                {{ number_format($item->length_cm, 0) }}×{{ number_format($item->width_cm, 0) }}×{{ number_format($item->height_cm, 0) }}
                            </td>
                            
                            {{-- BERAT --}}
                            <td class="px-4 py-3 text-right text-gray-600">
                                {{ number_format($item->weight_kg, 1) }}
                            </td>
                            
                            {{-- KOLOM STATUS (WAJIB) --}}
                            <td class="px-4 py-3 text-center">
                                @if($item->status === 'menunggu')
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">⏳ Menunggu</span>
                                @elseif($item->status === 'terkirim')
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">✅ Terkirim</span>
                                @elseif($item->status === 'carryover')
                                    <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs font-medium">⚠️ Carry-over</span>
                                @else
                                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs font-medium">{{ $item->status }}</span>
                                @endif
                            </td>
                            
                            {{-- ASAL -> TUJUAN --}}
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $item->depot_asal ?? '-' }} <span class="text-gray-400">→</span> {{ $item->kota_tujuan ?? '-' }}
                            </td>
                            
                            {{-- TANGGAL --}}
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->order_date)->format('d M Y') }}
                            </td>
                            
                            {{-- AKSI (HANYA EDIT) --}}
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('items.edit', $item->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline text-xs font-medium">
                                    Edit Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-500">
                    Menampilkan {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} data
                </p>
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>
@endsection