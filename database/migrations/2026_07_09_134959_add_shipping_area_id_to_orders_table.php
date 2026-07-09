<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->foreignId('shipping_area_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('shipping_areas')
                  ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropForeign(['shipping_area_id']);
            $table->dropColumn('shipping_area_id');

        });
    }
};