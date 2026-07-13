<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Berapa gram yang direpresentasikan 1 pcs varian ini (contoh: 1kg = 1000)
            $table->integer('weight')->default(0)->after('variant_name');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};