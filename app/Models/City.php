<?php
// app/Models/City.php
// Mirror dari db/models.py → class City
// Pola 1: Shared DB — model ini baca tabel cities yang sama dengan Streamlit

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $table = 'cities';

    protected $fillable = [
        'name', 'latitude', 'longitude',
        'is_depot', 'is_active',
        'max_truck_capacity', 'max_warehouse_kg',
    ];

    protected $casts = [
        'latitude'           => 'float',
        'longitude'          => 'float',
        'is_depot'           => 'boolean',
        'is_active'          => 'boolean',
        'max_truck_capacity' => 'integer',
        'max_warehouse_kg'   => 'float',
    ];

    // Relasi: satu kota bisa punya banyak truk (home depot)
    public function homeDepotTrucks(): HasMany
    {
        return $this->hasMany(Truck::class, 'home_depot_id');
    }

    // Relasi: truk yang sedang di kota ini
    public function currentTrucks(): HasMany
    {
        return $this->hasMany(Truck::class, 'current_city_id');
    }

    // Jarak dari kota ini ke kota lain
    public function distancesFrom(): HasMany
    {
        return $this->hasMany(DepotDistance::class, 'city_a_id');
    }

    // Scope: hanya kota aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: hanya depot aktif
    public function scopeDepot($query)
    {
        return $query->where('is_depot', true)->where('is_active', true);
    }
}
