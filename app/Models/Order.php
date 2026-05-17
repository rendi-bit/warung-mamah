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
        'has_waiting_restock',
        'restock_note',
        'payment_proof',
        'payment_confirmed_at',
        'shipped_at',
        'completed_at',
    ];

    protected $casts = [
        'shipped_at'           => 'datetime',
        'completed_at'         => 'datetime',
        'has_waiting_restock'  => 'boolean',
        'payment_confirmed_at' => 'datetime',
    ];

    // ─────────────────────────────────────────
    // RELASI AKTIF
    // ─────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ─────────────────────────────────────────
    // HELPER
    // ─────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCancellable(): bool
    {
        return !in_array($this->order_status, ['shipped', 'completed', 'cancelled'])
            && $this->created_at->gte(now()->subDay());
    }
}