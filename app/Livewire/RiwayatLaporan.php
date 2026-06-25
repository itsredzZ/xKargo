<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\SimulationResult;
use Carbon\Carbon;

class RiwayatLaporan extends Component
{
    public $startDate;
    public $endDate;
    public $data = [];

    public function mount()
    {
        $this->startDate = now()->subDays(7)->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        $this->data = SimulationResult::whereBetween('run_date', [
            $this->startDate, $this->endDate
        ])->get()->toArray();
    }

    public function render()
    {
        return view('livewire.riwayat-laporan')
            ->layout('layouts.app', ['title' => 'Riwayat & Laporan']);
    }
}