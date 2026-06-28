@extends('layouts.app')
@section('title', 'Edit Detail Barang')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-semibold text-gray-800">✏️ Edit Detail Barang</h1>
        <p class="text-gray-500 text-sm mt-0.5">Perbaiki data barang jika ada kesalahan input.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Order ID</p>
                    <p class="font-mono font-medium text-gray-800">#{{ $item->order_id }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Rute</p>
                    <p class="font-medium text-gray-800">{{ $item->depot_asal ?? '-' }} → {{ $item->kota_tujuan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal Pesan</p>
                    <p class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($item->order_date)->format('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Status Saat Ini</p>
                    <p class="font-medium text-gray-800">{{ ucfirst($item->status) }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('items.update', $item->id) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                    <input type="text" name="name" value="{{ $item->name }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dimensi (cm)</label>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Panjang</label>
                            <input type="number" name="length_cm" value="{{ $item->length_cm }}" step="0.1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Lebar</label>
                            <input type="number" name="width_cm" value="{{ $item->width_cm }}" step="0.1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tinggi</label>
                            <input type="number" name="height_cm" value="{{ $item->height_cm }}" step="0.1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berat (kg)</label>
                    <input type="number" name="weight_kg" value="{{ $item->weight_kg }}" step="0.1" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
                <a href="{{ route('items.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection