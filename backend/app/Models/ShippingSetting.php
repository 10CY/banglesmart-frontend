<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'flat_shipping_amount',
        'free_shipping_minimum',
        'shipping_enabled',
    ];

    protected $casts = [
        'flat_shipping_amount' =>
            'decimal:2',

        'free_shipping_minimum' =>
            'decimal:2',

        'shipping_enabled' =>
            'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Current Setting
    |--------------------------------------------------------------------------
    */

    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'flat_shipping_amount' =>
                    0,

                'free_shipping_minimum' =>
                    null,

                'shipping_enabled' =>
                    true,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Shipping Calculator
    |--------------------------------------------------------------------------
    */

    public function calculateShipping(
        float $subtotal
    ): float {
        if (
            !$this->shipping_enabled
        ) {
            return 0;
        }

        if (
            $this->free_shipping_minimum !==
                null
            &&
            $subtotal >=
                (float)
                $this->free_shipping_minimum
        ) {
            return 0;
        }

        return round(
            max(
                0,
                (float)
                $this->flat_shipping_amount
            ),
            2
        );
    }
}