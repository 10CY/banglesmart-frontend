<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->foreignId('coupon_id')
                ->nullable()
                ->after('discount_amount')
                ->constrained('coupons')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Snapshot coupon code so old orders keep it
            |--------------------------------------------------------------------------
            */

            $table->string('coupon_code')
                ->nullable()
                ->after('coupon_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropForeign([
                'coupon_id',
            ]);

            $table->dropColumn([
                'coupon_id',
                'coupon_code',
            ]);
        });
    }
};