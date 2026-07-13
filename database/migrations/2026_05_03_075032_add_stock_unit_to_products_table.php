<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Stok fisik total dalam GRAM, dipakai kalau produk punya varian berat.
            $table->decimal('base_stock', 12, 2)->default(0)->after('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('base_stock');
        });
    }
};