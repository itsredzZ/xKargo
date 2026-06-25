<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">📋 Riwayat & Laporan</h1>

    {{-- Filter Tanggal --}}
    <div class="flex gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium mb-1">Dari Tanggal</label>
            <input type="date" wire:model="startDate" wire:change="loadData"
                   class="border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Sampai Tanggal</label>
            <input type="date" wire:model="endDate" wire:change="loadData"
                   class="border rounded px-3 py-2">
        </div>
    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-right">Total Tarif</th>
                    <th class="px-4 py-3 text-right">Biaya BBM</th>
                    <th class="px-4 py-3 text-right">Profit Bersih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $row['run_date'] }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($row['tariff_total'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format($row['fuel_cost'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($row['net_profit'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Export --}}
    <div class="flex gap-3 mt-6">
        <a href="{{ route('laporan.excel', ['start' => $startDate, 'end' => $endDate]) }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            ⬇️ Export Excel
        </a>
        <a href="{{ route('laporan.pdf', ['start' => $startDate, 'end' => $endDate]) }}"
           class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            ⬇️ Export PDF
        </a>
    </div>
</div>