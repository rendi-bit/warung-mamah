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
     * ✅ FIX: Format angka stok tanpa desimal nol yang tidak perlu.
     * 20      → "20"
     * 20.5    → "20.5"
     * 20.25   → "20.25"
     * 20.256  → "20.26" (dibulatkan 2 desimal, baru trim nol)
     */
    private function formatQty($qty): string
    {
        $formatted = number_format((float) $qty, 2, '.', '');

        // Buang nol di belakang, lalu buang titik kalau jadi bilangan bulat
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, '.');

        return $formatted;
    }

    /**
     * ✅ FIX: Stok untuk tampilan USER
     * - Kalau produk PUNYA VARIAN → jumlahkan stock dari SEMUA varian (bukan stock_quantity produk utama,
     *   karena field itu tidak pernah dikurangi untuk produk bervarian).
     * - Kalau produk TANPA VARIAN → tetap pakai stock_quantity seperti semula.
     * - Kalau stok minus → tampilkan 0 (user tidak perlu tahu stok negatif).
     */
    public function getStockLabelAttribute()
    {
        if ($this->variants()->exists()) {
            $totalVariantStock = max(0, (int) $this->variants()->sum('stock'));

            return $totalVariantStock . ' ' . $this->stock_unit;
        }

        $qty = max(0, $this->stock_quantity);

        return intval($qty) . ' ' . $this->stock_unit;
    }

    /**
     * Stok untuk tampilan ADMIN — tampilkan nilai asli termasuk minus.
     * ✅ FIX: Untuk produk bervarian, tampilkan total stok varian (termasuk minus),
     * bukan stock_quantity produk utama yang tidak relevan lagi.
     */
    public function getAdminStockLabelAttribute()
    {
        if ($this->variants()->exists()) {
            $totalVariantStock = (int) $this->variants()->sum('stock');

            return $totalVariantStock . ' ' . $this->stock_unit . ' (total dari semua varian)';
        }

        if ($this->stock_mode === 'dus') {
            return ($this->box_stock ?? 0) . ' dus x ' .
                ($this->unit_per_box ?? 0) . ' ' .
                $this->stock_unit . ' = ' .
                $this->stock_quantity . ' ' .
                $this->stock_unit;
        }

        return $this->formatQty($this->stock_quantity) . ' ' . $this->stock_unit;
    }
}