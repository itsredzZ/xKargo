<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'origin_depot_id', 
        'destination_city_id', 
        'order_date', 
        'status', 
        'source', 
        'created_by', 
        'notes'
    ];

    // SATU pesanan bisa punya BANYAK barang fisik (Sesuai DB baru)
    public function items()
    {
        return $this->hasMany(Item::class, 'order_id');
    }

    // Pesanan ini berangkat dari depot mana?
    public function originDepot()
    {
        return $this->belongsTo(City::class, 'origin_depot_id');
    }

    // Pesanan ini ditujukan ke kota mana?
    public function destinationCity()
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }
}