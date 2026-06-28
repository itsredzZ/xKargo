@extends('layouts.app')
@section('title', 'Riwayat & Laporan')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-800">📋 Riwayat & Laporan</h1>
    </div>

    {{-- Filter Tanggal --}}
    <form method="GET" action="{{ route('riwayat.index') }}"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-4 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="start" value="{{ $startDate }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="end" value="{{ $endDate }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            🔍 Filter
        </button>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('laporan.excel', ['start' => $startDate, 'end' => $endDate]) }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                ⬇️ Export Excel
            </a>
            <a href="{{ route('laporan.pdf', ['start' => $startDate, 'end' => $endDate]) }}"
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                ⬇️ Export PDF
            </a>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs text-gray-500 mb-1">Total Tarif</p>
            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalTarif, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs text-gray-500 mb-1">Total Biaya BBM</p>
            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalBbm, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs text-gray-500 mb-1">Total Profit Bersih</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalProfit, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-blue-800 text-white">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Truk</th>
                    <th class="px-4 py-3 text-right">Total Tarif</th>
                    <th class="px-4 py-3 text-right">Biaya BBM</th>
                    <th class="px-4 py-3 text-right">Profit Bersih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr class="border-t hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($row->run_date)->format('Y-m-d') }}</td>
                    <td class="px-4 py-3">Truk #{{ $row->truck_id }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($row->tariff_total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($row->fuel_cost, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-green-600">
                        Rp {{ number_format($row->net_profit, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                        Belum ada data simulasi pada periode ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection