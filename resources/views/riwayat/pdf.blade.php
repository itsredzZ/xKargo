<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat XKargo</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p.periode { font-size: 11px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead { background-color: #1e3a8a; color: white; }
        th { padding: 8px 10px; text-align: left; font-size: 11px; }
        td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .summary { margin-top: 12px; background: #f0f9ff; border: 1px solid #bae6fd; padding: 12px 16px; }
        .summary p { margin: 4px 0; font-size: 12px; }
        .summary strong { color: #1e3a8a; }
        .footer { margin-top: 24px; font-size: 10px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Riwayat XKargo</h1>
    <p class="periode">Periode: {{ $startDate }} s/d {{ $endDate }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Truk</th>
                <th class="text-right">Total Tarif (Rp)</th>
                <th class="text-right">Biaya BBM (Rp)</th>
                <th class="text-right">Profit Bersih (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row->run_date)->format('d/m/Y') }}</td>
                <td>Truk #{{ $row->truck_id }}</td>
                <td class="text-right">{{ number_format($row->tariff_total, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row->fuel_cost, 0, ',', '.') }}</td>
                <td class="text-right"><strong>{{ number_format($row->net_profit, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; color:#9ca3af; padding:16px;">
                    Belum ada data simulasi pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <p>Total Tarif: <strong>Rp {{ number_format($totalTarif ?? 0, 0, ',', '.') }}</strong></p>
        <p>Total Biaya BBM: <strong>Rp {{ number_format($totalBbm ?? 0, 0, ',', '.') }}</strong></p>
        <p>Total Profit Bersih: <strong>Rp {{ number_format($totalProfit ?? 0, 0, ',', '.') }}</strong></p>
        <p>Total Data: <strong>{{ $data->count() }} simulasi</strong></p>
    </div>

    <div class="footer">
        XKargo — Sistem Optimasi Distribusi Barang Multi-Depot |
        Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>