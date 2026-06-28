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
                    \Carbon\Carbon::parse($row->run_date)->format('d/m/Y'), // ← fix format
                    'Truk #' . $row->truck_id,
                    number_format($row->tariff_total, 0, ',', '.'),
                    number_format($row->fuel_cost, 0, ',', '.'),
                    number_format($row->net_profit, 0, ',', '.'),
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
        $totalTarif  = $data->sum('tariff_total');
        $totalBbm    = $data->sum('fuel_cost');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('riwayat.pdf', compact(
            'data', 'startDate', 'endDate',
            'totalProfit', 'totalTarif', 'totalBbm'  // ← tambah totalTarif & totalBbm
        ))->setPaper('a4', 'landscape');  // ← landscape agar tabel tidak terpotong

        return $pdf->download("laporan_{$startDate}_{$endDate}.pdf");
    }
}