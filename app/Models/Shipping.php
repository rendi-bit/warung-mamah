<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    protected $fillable = [
        'order_id',
        'shipping_method',
        'shipping_status',
        'tracking_number',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}