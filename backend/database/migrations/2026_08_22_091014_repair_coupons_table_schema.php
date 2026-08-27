<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Coupon Code
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('coupons', 'code')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->string('code')
                    ->nullable()
                    ->unique();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Discount Type
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('coupons', 'type')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->string('type')
                    ->default('fixed');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Discount Value
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('coupons', 'value')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->decimal(
                    'value',
                    12,
                    2
                )->default(0);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Order Amount
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn(
            'coupons',
            'minimum_order_amount'
        )) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->decimal(
                    'minimum_order_amount',
                    12,
                    2
                )->default(0);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Discount Amount
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn(
            'coupons',
            'maximum_discount_amount'
        )) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->decimal(
                    'maximum_discount_amount',
                    12,
                    2
                )->nullable();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Total Usage Limit
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn(
            'coupons',
            'usage_limit'
        )) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->unsignedInteger(
                    'usage_limit'
                )->nullable();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Per Customer Usage Limit
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn(
            'coupons',
            'per_user_limit'
        )) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->unsignedInteger(
                    'per_user_limit'
                )->default(1);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Start Date
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn(
            'coupons',
            'starts_at'
        )) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->timestamp(
                    'starts_at'
                )->nullable();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Expiry Date
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn(
            'coupons',
            'expires_at'
        )) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->timestamp(
                    'expires_at'
                )->nullable();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn(
            'coupons',
            'status'
        )) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->string(
                    'status'
                )->default('active');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Timestamps
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn(
            'coupons',
            'created_at'
        )) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->timestamp(
                    'created_at'
                )->nullable();
            });
        }

        if (!Schema::hasColumn(
            'coupons',
            'updated_at'
        )) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->timestamp(
                    'updated_at'
                )->nullable();
            });
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Intentionally Empty
        |--------------------------------------------------------------------------
        |
        | This is a repair migration. We do not want rollback to remove existing
        | coupon data or columns.
        |
        */
    }
};