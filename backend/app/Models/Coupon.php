<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_order_amount',
        'maximum_discount_amount',
        'usage_limit',
        'per_user_limit',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',

        'minimum_order_amount' =>
            'decimal:2',

        'maximum_discount_amount' =>
            'decimal:2',

        'usage_limit' =>
            'integer',

        'per_user_limit' =>
            'integer',

        'starts_at' =>
            'datetime',

        'expires_at' =>
            'datetime',
    ];

    public function usages()
    {
        return $this->hasMany(
            CouponUsage::class
        );
    }

    public function orders()
    {
        return $this->hasMany(
            Order::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Discount
    |--------------------------------------------------------------------------
    */

    public function calculateDiscount(
        float $subtotal
    ): float {
        $discount = 0;

        /*
        |--------------------------------------------------------------------------
        | Percentage
        |--------------------------------------------------------------------------
        */

        if (
            $this->type ===
            'percentage'
        ) {
            $discount =
                $subtotal *
                (
                    (float) $this->value /
                    100
                );

            if (
                $this
                    ->maximum_discount_amount
                !== null
            ) {
                $discount =
                    min(
                        $discount,
                        (float)
                        $this
                            ->maximum_discount_amount
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fixed
        |--------------------------------------------------------------------------
        */

        if (
            $this->type ===
            'fixed'
        ) {
            $discount =
                (float)
                $this->value;
        }

        /*
        |--------------------------------------------------------------------------
        | Coupon Can Never Make Subtotal Negative
        |--------------------------------------------------------------------------
        */

        $discount =
            min(
                $discount,
                $subtotal
            );

        return round(
            max(
                0,
                $discount
            ),
            2
        );
    }
}