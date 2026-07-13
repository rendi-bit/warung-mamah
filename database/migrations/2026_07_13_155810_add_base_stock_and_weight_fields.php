<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stok fisik total dalam GRAM, dipakai kalau produk punya varian berat.
        if (!Schema::hasColumn('products', 'base_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('base_stock', 12, 2)->default(0)->after('stock_quantity');
            });
        }

        // Berapa gram yang direpresentasikan 1 pcs varian ini (contoh: 1kg = 1000)
        if (!Schema::hasColumn('product_variants', 'weight')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->integer('weight')->default(0)->after('variant_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'base_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('base_stock');
            });
        }

        if (Schema::hasColumn('product_variants', 'weight')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('weight');
            });
        }
    }
};