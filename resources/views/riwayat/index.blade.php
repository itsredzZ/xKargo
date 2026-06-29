@extends('layouts.app')
@section('title', 'Riwayat & Laporan')

@section('content')
    <div class="space-y-6">

        {{-- ── HEADER ─────────────────────────────────────────────────────── --}}
        <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-blue-700 flex items-center justify-center shadow-md shadow-blue-100 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0
                   012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1
                   0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat dan Laporan</h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Pantau rekam jejak operasional dan analisis ringkasan performa pengiriman Anda
                    </p>
                </div>
            </div>
        </header>

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
            Filter
        </button>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('laporan.excel', ['start' => $startDate, 'end' => $endDate]) }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Export Excel
            </a>
            <a href="{{ route('laporan.pdf', ['start' => $startDate, 'end' => $endDate]) }}"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Export PDF
            </a>
        </div>
    </form>

    <br> 

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

    <br> 
    
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
                        <td class="px-4 py-3">Truk {{ $row->truck->plate_number }}</td>
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
