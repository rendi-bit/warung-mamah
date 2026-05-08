<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'is_waiting_restock',
        'waiting_restock_quantity',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    
    public function variant()
    {
    return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}