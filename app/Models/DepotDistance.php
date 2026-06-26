<?php
// app/Models/DepotDistance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepotDistance extends Model
{
    protected $table = 'depot_distances';

    protected $fillable = [
        'city_a_id', 'city_b_id',
        'distance_km', 'duration_minutes',
    ];

    protected $casts = [
        'distance_km'      => 'float',
        'duration_minutes' => 'integer',
    ];

    public function cityA(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_a_id');
    }

    public function cityB(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_b_id');
    }
}
