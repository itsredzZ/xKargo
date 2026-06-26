<?php
// app/Models/SimulationResult.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationResult extends Model
{
    protected $table = 'simulation_results';

    protected $fillable = [
        'run_date', 'truck_id',
        'route_json', 'total_weight_kg', 'total_volume_m3',
        'tariff_total', 'fuel_cost', 'net_profit',
        'gbest_curve_json',
    ];

    protected $casts = [
        'run_date'         => 'date',
        'route_json'       => 'array',
        'gbest_curve_json' => 'array',
        'total_weight_kg'  => 'float',
        'total_volume_m3'  => 'float',
        'tariff_total'     => 'float',
        'fuel_cost'        => 'float',
        'net_profit'       => 'float',
        'created_at' => 'datetime',
    ];

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}
