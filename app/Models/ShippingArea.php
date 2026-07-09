<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingArea extends Model
{
    protected $fillable = [
        'kelurahan',
        'shipping_cost'
    ];
}