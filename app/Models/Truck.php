<?php
// app/Models/Truck.php
// Mirror dari db/models.py → class Truck

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Truck extends Model
{
    protected $table = 'trucks';

    protected $fillable = [
        'plate_number',
        'max_weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'home_depot_id',
        'current_city_id',
        'is_active',
        'operational_status',
        'truck_type',
        'fuel_efficiency_km_per_liter',
    ];

    protected $casts = [
        'max_weight_kg'                => 'float',
        'length_cm'                    => 'float',
        'width_cm'                     => 'float',
        'height_cm'                    => 'float',
        'is_active'                    => 'boolean',
        'fuel_efficiency_km_per_liter' => 'float',
    ];

    // Label tipe truk — sinkron dengan TRUCK_PRESETS di 2_Master_Data_Truk.py
    const TYPE_LABELS = [
        'mobil_box'   => 'Mobil Box',
        'pickup'      => 'Pickup',
        'cde'         => 'CDE (Engkel)',
        'cdd'         => 'CDD (6 Roda)',
        'cdd_long'    => 'CDD Long',
        'fuso'        => 'Fuso Box',
        'tronton'     => 'Tronton',
        'prime_mover' => 'Trailer 40ft',
    ];

    const STATUS_LABELS = [
        'available'   => 'Tersedia',
        'on_duty'     => 'Sedang Jalan',
        'maintenance' => 'Maintenance',
    ];

    // Volume dalam m³
    public function getVolumem3Attribute(): float
    {
        return ($this->length_cm * $this->width_cm * $this->height_cm) / 1_000_000;
    }

    // Label tipe truk
    public function getTruckTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->truck_type] ?? $this->truck_type;
    }

    // Scope: hanya truk available (untuk PSO engine)
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where('operational_status', 'available');
    }

    public function homeDepot(): BelongsTo
    {
        return $this->belongsTo(City::class, 'home_depot_id');
    }

    public function currentCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'current_city_id');
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function simulationResults()
    {
        return $this->hasMany(SimulationResult::class);
    }
}
