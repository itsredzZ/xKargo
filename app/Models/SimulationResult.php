<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SimulationResult extends Model
{
    protected $table = 'simulation_results';
    public $timestamps = false;
    protected $fillable = [
        'run_date', 'truck_id', 'route_json',
        'total_weight_kg', 'total_volume_m3',
        'tariff_total', 'fuel_cost', 'net_profit', 'gbest_curve_json'
    ];
}