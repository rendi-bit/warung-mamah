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

    // ─────────────────────────────────────────
    // RELASI AKTIF
    // ─────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─────────────────────────────────────────
    // HELPER / ACCESSOR
    // ─────────────────────────────────────────

    public function getDisplayPriceAttribute()
    {
        return $this->variants->count()
            ? $this->variants->min('price')
            : $this->price;
    }

    /**
     * ✅ FIX: Stok untuk tampilan USER
     * Kalau stok minus → tampilkan 0 (user tidak perlu tahu stok negatif)
     * Kalau stok normal → tampilkan apa adanya
     */
    public function getStockLabelAttribute()
    {
        $qty = max(0, $this->stock_quantity);
        return $qty . ' ' . $this->stock_unit;
    }

    /**
     * Stok untuk tampilan ADMIN — tampilkan nilai asli termasuk minus
     */
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