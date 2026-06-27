<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 
        'name', 
        'length_cm', 
        'width_cm', 
        'height_cm', 
        'weight_kg', 
        'status', 
        'is_carryover'
    ];

    // SATU barang dimiliki oleh SATU pesanan (Sesuai DB baru: items.order_id -> delivery_orders.id)
    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'order_id');
    }
}