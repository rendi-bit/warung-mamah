<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
    'name',
    'slug',
    'description',
    'price',
    'stock_quantity',
    'stock_unit',
    'category_id',
    'user_id',
    'image',
    'status',
];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
    return $this->hasMany(ProductReview::class);
    }

    public function variants()
    {
    return $this->hasMany(ProductVariant::class);
    }

    public function getDisplayPriceAttribute()
    {
        return $this->variants->count()
        ? $this->variants->min('price')
        : $this->price;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStockLabelAttribute()
    {
        return $this->stock_quantity . ' ' . $this->stock_unit;
    }
}