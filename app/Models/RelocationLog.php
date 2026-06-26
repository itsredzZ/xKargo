<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelocationLog extends Model
{
    use HasFactory;

    protected $table = 'relocation_logs';

    protected $fillable = [
        'truck_id', 'from_depot_id', 'to_depot_id', 
        'relocation_cost', 'relocation_date'
    ];

    public function truck()
    {
        return $this->belongsTo(Truck::class, 'truck_id');
    }

    public function fromDepot()
    {
        return $this->belongsTo(City::class, 'from_depot_id');
    }

    public function toDepot()
    {
        return $this->belongsTo(City::class, 'to_depot_id');
    }
}