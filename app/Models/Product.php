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
        'stock_mode',
        'unit_per_box',
        'box_stock',
        'restock_estimation',
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

    public function getAdminStockLabelAttribute()
    {
        if ($this->stock_mode === 'dus') {
            return ($this->box_stock ?? 0) . ' dus x ' .
                ($this->unit_per_box ?? 0) . ' ' .
                $this->stock_unit . ' = ' .
                $this->stock_quantity . ' ' .
                $this->stock_unit;
        }

        return $this->stock_quantity . ' ' . $this->stock_unit;
    }
}