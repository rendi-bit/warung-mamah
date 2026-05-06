<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_whatsapp')) {
                $table->string('customer_whatsapp')->nullable()->after('shipping_address');
            }

            if (!Schema::hasColumn('orders', 'house_landmark')) {
                $table->string('house_landmark')->nullable()->after('customer_whatsapp');
            }

            if (!Schema::hasColumn('orders', 'delivery_method')) {
                $table->string('delivery_method')->default('ojek_toko')->after('house_landmark');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivery_method')) {
                $table->dropColumn('delivery_method');
            }

            if (Schema::hasColumn('orders', 'house_landmark')) {
                $table->dropColumn('house_landmark');
            }

            if (Schema::hasColumn('orders', 'customer_whatsapp')) {
                $table->dropColumn('customer_whatsapp');
            }
        });
    }
};