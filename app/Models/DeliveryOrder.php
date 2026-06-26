<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'origin_depot_id', 'destination_city_id', 
        'quantity', 'order_date', 'status'
    ];

    // Relasi: Pesanan ini memuat barang apa?
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    // Relasi: Pesanan ini tujuannya ke kota mana?
    public function destination()
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }
}