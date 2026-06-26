<?php
namespace App\Http\Controllers;

use App\Models\SimulationResult;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start', now()->subDays(7)->format('Y-m-d'));
        $endDate   = $request->get('end',   now()->format('Y-m-d'));

        $data = SimulationResult::whereBetween('run_date', [$startDate, $endDate])
            ->orderByDesc('run_date')
            ->get();

        $totalProfit = $data->sum('net_profit');
        $totalTarif  = $data->sum('tariff_total');
        $totalBbm    = $data->sum('fuel_cost');

        return view('riwayat.index', compact(
            'data', 'startDate', 'endDate',
            'totalProfit', 'totalTarif', 'totalBbm'
        ));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->get('start', now()->subDays(7)->format('Y-m-d'));
        $endDate   = $request->get('end',   now()->format('Y-m-d'));

        $data = SimulationResult::whereBetween('run_date', [$startDate, $endDate])
            ->orderByDesc('run_date')
            ->get();

        $filename = "laporan_{$startDate}_{$endDate}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Truk ID', 'Total Tarif (Rp)', 'Biaya BBM (Rp)', 'Profit Bersih (Rp)']);
            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->run_date,
                    $row->truck_id,
                    $row->tariff_total,
                    $row->fuel_cost,
                    $row->net_profit,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start', now()->subDays(7)->format('Y-m-d'));
        $endDate   = $request->get('end',   now()->format('Y-m-d'));

        $data = SimulationResult::whereBetween('run_date', [$startDate, $endDate])
            ->orderByDesc('run_date')
            ->get();

        $totalProfit = $data->sum('net_profit');

        // Generate PDF pakai view
        $html = view('riwayat.pdf', compact('data', 'startDate', 'endDate', 'totalProfit'))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"laporan_{$startDate}_{$endDate}.pdf\"");
    }
}