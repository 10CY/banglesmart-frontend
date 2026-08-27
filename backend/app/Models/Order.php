<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',

        'status',

        'payment_method',
        'payment_status',
        'courier_name',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'cancelled_at',

        'subtotal',
        'shipping_amount',
        'discount_amount',

        'coupon_id',
        'coupon_code',

        'total_amount',

        'shipping_address',
        'billing_address',

        'customer_note',
    ];

    protected $casts = [
        'user_id' =>
            'integer',

        'coupon_id' =>
            'integer',

        'subtotal' =>
            'decimal:2',

        'shipping_amount' =>
            'decimal:2',

        'discount_amount' =>
            'decimal:2',

        'total_amount' =>
            'decimal:2',

        'shipping_address' =>
            'array',

        'billing_address' =>
            'array',

        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Customer
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Items
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(
            OrderItem::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Coupon
    |--------------------------------------------------------------------------
    */

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function coupon()
    {
        return $this->belongsTo(
            Coupon::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Coupon Usage
    |--------------------------------------------------------------------------
    */

    public function couponUsage()
    {
        return $this->hasOne(
            CouponUsage::class
        );
    }
}