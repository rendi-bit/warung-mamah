<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->boolean('need_reupload')
                  ->default(false)
                  ->after('payment_status');

            $table->text('reupload_note')
                  ->nullable()
                  ->after('need_reupload');

        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropColumn([
                'need_reupload',
                'reupload_note'
            ]);

        });
    }
};