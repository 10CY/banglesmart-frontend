<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Validate Coupon
    |--------------------------------------------------------------------------
    */

    public function validateCoupon(
        Request $request
    ) {
        $validated =
            $request->validate([
                'code' => [
                    'required',
                    'string',
                    'max:50',
                ],
            ]);

        $user =
            $request->user();

        /*
        |--------------------------------------------------------------------------
        | Find Coupon
        |--------------------------------------------------------------------------
        */

        $code =
            Str::upper(
                trim(
                    $validated['code']
                )
            );

        $coupon =
            Coupon::where(
                'code',
                $code
            )->first();

        if (!$coupon) {
            return $this->invalid(
                'Invalid coupon code.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $coupon->status !==
            'active'
        ) {
            return $this->invalid(
                'This coupon is currently inactive.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Start Date
        |--------------------------------------------------------------------------
        */

        if (
            $coupon->starts_at &&
            now()->lt(
                $coupon->starts_at
            )
        ) {
            return $this->invalid(
                'This coupon is not active yet.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Expiry
        |--------------------------------------------------------------------------
        */

        if (
            $coupon->expires_at &&
            now()->gt(
                $coupon->expires_at
            )
        ) {
            return $this->invalid(
                'This coupon has expired.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Global Usage
        |--------------------------------------------------------------------------
        |
        | Cancelled orders do not count against coupon limits.
        |
        */

        $globalUsage =
            CouponUsage::where(
                'coupon_id',
                $coupon->id
            )
                ->whereHas(
                    'order',
                    function ($query) {
                        $query->where(
                            'status',
                            '!=',
                            'cancelled'
                        );
                    }
                )
                ->count();

        if (
            $coupon->usage_limit !== null &&
            $globalUsage >=
                $coupon->usage_limit
        ) {
            return $this->invalid(
                'This coupon has reached its usage limit.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Customer Usage
        |--------------------------------------------------------------------------
        */

        $customerUsage =
            CouponUsage::where(
                'coupon_id',
                $coupon->id
            )
                ->where(
                    'user_id',
                    $user->id
                )
                ->whereHas(
                    'order',
                    function ($query) {
                        $query->where(
                            'status',
                            '!=',
                            'cancelled'
                        );
                    }
                )
                ->count();

        if (
            $customerUsage >=
            $coupon->per_user_limit
        ) {
            return $this->invalid(
                'You have already used this coupon the maximum number of times.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */

        $cart =
            Cart::where(
                'user_id',
                $user->id
            )
                ->with([
                    'items.variant.product',
                ])
                ->first();

        if (
            !$cart ||
            $cart->items->isEmpty()
        ) {
            return $this->invalid(
                'Your cart is empty.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate Cart Subtotal From Database
        |--------------------------------------------------------------------------
        */

        $subtotal = 0;

        foreach (
            $cart->items
            as $item
        ) {
            $variant =
                $item->variant;

            if (
                !$variant ||
                !$variant->product
            ) {
                continue;
            }

            if (
                $variant->status !==
                    'active' ||
                $variant->product
                    ->status !==
                    'active'
            ) {
                continue;
            }

            $subtotal +=
                (
                    (float)
                    $variant
                        ->selling_price
                )
                *
                (
                    (int)
                    $item->quantity
                );
        }

        $subtotal =
            round(
                $subtotal,
                2
            );

        if (
            $subtotal <= 0
        ) {
            return $this->invalid(
                'Your cart does not contain valid products.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Order Value
        |--------------------------------------------------------------------------
        */

        if (
            $subtotal <
            (float)
            $coupon
                ->minimum_order_amount
        ) {
            return $this->invalid(
                'Minimum order value of ₹' .
                number_format(
                    (float)
                    $coupon
                        ->minimum_order_amount,
                    0
                ) .
                ' is required for this coupon.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate Discount
        |--------------------------------------------------------------------------
        */

        $discountAmount =
            $coupon
                ->calculateDiscount(
                    $subtotal
                );

        $total =
            max(
                0,

                $subtotal -
                $discountAmount
            );

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Coupon applied successfully.',

            'data' => [
                'coupon' => [
                    'id' =>
                        $coupon->id,

                    'code' =>
                        $coupon->code,

                    'type' =>
                        $coupon->type,

                    'value' =>
                        $coupon->value,

                    'minimum_order_amount' =>
                        $coupon
                            ->minimum_order_amount,

                    'maximum_discount_amount' =>
                        $coupon
                            ->maximum_discount_amount,
                ],

                'subtotal' =>
                    number_format(
                        $subtotal,
                        2,
                        '.',
                        ''
                    ),

                'discount_amount' =>
                    number_format(
                        $discountAmount,
                        2,
                        '.',
                        ''
                    ),

                'total_after_discount' =>
                    number_format(
                        $total,
                        2,
                        '.',
                        ''
                    ),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Invalid Coupon Response
    |--------------------------------------------------------------------------
    */

    private function invalid(
        string $message
    ) {
        return response()->json([
            'success' =>
                false,

            'message' =>
                $message,
        ], 422);
    }
}