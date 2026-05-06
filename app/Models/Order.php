<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_code',
        'user_id',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'grand_total',
        'payment_method',
        'payment_status',
        'order_status',
        'shipping_address',
        'customer_whatsapp',
        'house_landmark',
        'delivery_method',
        'notes',
    ];
    
    protected $casts = [
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class);
    }
}