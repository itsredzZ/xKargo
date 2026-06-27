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
        .summary { margin-top: 12px; background: #f0f9ff; border: 1px solid #bae6fd; padding: 12px 16px; border-radius: 6px; }
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
                <th>Truk ID</th>
                <th class="text-right">Total Tarif