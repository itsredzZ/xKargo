<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name', 'weight_kg', 'length_cm', 'width_cm', 'height_cm'
    ];

    // Satu jenis barang bisa ada di banyak antrean pesanan
    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'item_id');
    }
}