<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'is_waiting_restock')) {
                $table->boolean('is_waiting_restock')->default(false)->after('quantity');
            }

            if (!Schema::hasColumn('cart_items', 'waiting_restock_quantity')) {
                $table->integer('waiting_restock_quantity')->default(0)->after('is_waiting_restock');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'is_waiting_restock')) {
                $table->boolean('is_waiting_restock')->default(false)->after('subtotal');
            }

            if (!Schema::hasColumn('order_items', 'waiting_restock_quantity')) {
                $table->integer('waiting_restock_quantity')->default(0)->after('is_waiting_restock');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'has_waiting_restock')) {
                $table->boolean('has_waiting_restock')->default(false)->after('notes');
            }

            if (!Schema::hasColumn('orders', 'restock_note')) {
                $table->text('restock_note')->nullable()->after('has_waiting_restock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'waiting_restock_quantity')) {
                $table->dropColumn('waiting_restock_quantity');
            }

            if (Schema::hasColumn('cart_items', 'is_waiting_restock')) {
                $table->dropColumn('is_waiting_restock');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'waiting_restock_quantity')) {
                $table->dropColumn('waiting_restock_quantity');
            }

            if (Schema::hasColumn('order_items', 'is_waiting_restock')) {
                $table->dropColumn('is_waiting_restock');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'restock_note')) {
                $table->dropColumn('restock_note');
            }

            if (Schema::hasColumn('orders', 'has_waiting_restock')) {
                $table->dropColumn('has_waiting_restock');
            }
        });
    }
};