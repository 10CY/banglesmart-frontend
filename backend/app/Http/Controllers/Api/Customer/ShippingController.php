<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ShippingSetting;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function quote(
        Request $request
    ) {
        $user =
            $request->user();

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
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your cart is empty.',
            ], 422);
        }

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

        $setting =
            ShippingSetting::current();

        $shippingAmount =
            $setting
                ->calculateShipping(
                    $subtotal
                );

        return response()->json([
            'success' =>
                true,

            'data' => [
                'subtotal' =>
                    number_format(
                        $subtotal,
                        2,
                        '.',
                        ''
                    ),

                'shipping_amount' =>
                    number_format(
                        $shippingAmount,
                        2,
                        '.',
                        ''
                    ),

                'flat_shipping_amount' =>
                    $setting
                        ->flat_shipping_amount,

                'free_shipping_minimum' =>
                    $setting
                        ->free_shipping_minimum,

                'shipping_enabled' =>
                    $setting
                        ->shipping_enabled,

                'free_shipping' =>
                    $shippingAmount <= 0,
            ],
        ]);
    }
}