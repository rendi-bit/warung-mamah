<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'stock_unit')) {
                $table->string('stock_unit')->default('pcs')->after('stock_quantity');
            }

            if (!Schema::hasColumn('products', 'stock_mode')) {
                $table->enum('stock_mode', ['satuan', 'dus'])->default('satuan')->after('stock_unit');
            }

            if (!Schema::hasColumn('products', 'unit_per_box')) {
                $table->integer('unit_per_box')->nullable()->after('stock_mode');
            }

            if (!Schema::hasColumn('products', 'box_stock')) {
                $table->integer('box_stock')->nullable()->after('unit_per_box');
            }

            if (!Schema::hasColumn('products', 'restock_estimation')) {
                $table->string('restock_estimation')->nullable()->after('box_stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'restock_estimation')) {
                $table->dropColumn('restock_estimation');
            }

            if (Schema::hasColumn('products', 'box_stock')) {
                $table->dropColumn('box_stock');
            }

            if (Schema::hasColumn('products', 'unit_per_box')) {
                $table->dropColumn('unit_per_box');
            }

            if (Schema::hasColumn('products', 'stock_mode')) {
                $table->dropColumn('stock_mode');
            }

            if (Schema::hasColumn('products', 'stock_unit')) {
                $table->dropColumn('stock_unit');
            }
        });
    }
};