<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarryoverItem extends Model
{
    use HasFactory;

    protected $table = 'carryover_items';

    protected $fillable = [
        'delivery_order_id', 'reason', 'carryover_date'
    ];

    // Relasi kembali ke pesanan aslinya
    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }
}