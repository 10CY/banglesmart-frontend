<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\ShippingSetting;
use App\Services\OrderEmailService;
use App\Services\InvoicePdfService;

class OrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Customer Orders
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        $orders =
            Order::where(
                'user_id',
                $request->user()->id
            )
                ->withCount(
                    'items'
                )
                ->latest()
                ->paginate(20);

        return response()->json([
            'success' =>
                true,

            'data' =>
                $orders,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Show Customer Order
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Order $order
    ) {
        if (
            (int)
            $order->user_id
            !==
            (int)
            $request->user()->id
        ) {
            abort(
                404,
                'Order not found.'
            );
        }

        $order->load([
            'items',
            'coupon',
        ]);

        return response()->json([
            'success' =>
                true,

            'data' =>
                $order,
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | Cancel Order
    |--------------------------------------------------------------------------
    */


    public function cancel(
        Request $request,
        $id
    ) {

        $user = $request->user();

        $order = $user
            ->orders()
            ->with('items')
            ->where('id', $id)
            ->first();

        if (!$order) {

            return response()->json([

                'success' => false,

                'message' => 'Order not found.'

            ],404);

        }

        if (
            $order->status !== 'pending'
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    'This order cannot be cancelled.'

            ],422);

        }


        DB::transaction(function () use ($order, $user) {

            foreach($order->items as $item){

                $inventory =
                    Inventory::where(
                        'product_variant_id',
                        $item->product_variant_id
                    )
                    ->lockForUpdate()
                    ->first();

                if($inventory){
                    $beforeAvailable =
                        max(
                            0,

                            (int)$inventory->quantity
                            -
                            (int)$inventory->reserved_quantity
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Release Reserved Stock
                    |--------------------------------------------------------------------------
                    */

                    $inventory->update([

                        'reserved_quantity' =>
                            max(
                                0,
                                $inventory->reserved_quantity
                                -
                                $item->quantity
                            ),

                    ]);

                    $afterAvailable =
                        max(
                            0,

                            (int)$inventory->quantity
                            -
                            (int)$inventory->reserved_quantity
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Inventory History
                    |--------------------------------------------------------------------------
                    */

                    InventoryMovement::create([

                        'product_variant_id' =>
                            $item->product_variant_id,


                        'user_id' =>
                            $user->id,


                        'type' =>
                            'order_cancelled',


                        'quantity' =>
                            $item->quantity,


                        'before_quantity' =>
                            $beforeAvailable,


                        'after_quantity' =>
                            $afterAvailable,


                        'reference_type' =>
                            'order',


                        'reference_id' =>
                            $order->id,


                        'notes' =>
                            "Stock released after cancelling order {$order->order_number}",

                    ]);

                }

            }

            $order->update([

                'status' =>
                    'cancelled'

            ]);

        });

        $order->load(['user', 'items', 'invoice']);
        if ($order->user?->email) {
            $emailService = app(OrderEmailService::class);
            app()->terminating(function () use ($emailService, $order) {
                try {
                    $emailService->sendStatus($order);
                } catch (\Throwable $mailException) {
                    logger()->warning('Cancellation email could not be sent with PHP mail().', [
                        'order_id' => $order->id,
                        'error' => $mailException->getMessage(),
                    ]);
                }
            });
        }

        return response()->json([

            'success'=>true,
            'message'=>
                'Order cancelled successfully.',
            'data'=>
                $order->fresh()->load('items'),
        ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Place Order
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {
        $validated =
            $request->validate([
                'shipping_address_id' => [
                    'required',
                    'integer',
                    'exists:addresses,id',
                ],

                'billing_address_id' => [
                    'nullable',
                    'integer',
                    'exists:addresses,id',
                ],

                'payment_method' => [
                    'required',

                    Rule::in([
                        'cod',
                    ]),
                ],

                'customer_note' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'coupon_code' => [
                    'nullable',
                    'string',
                    'max:50',
                ],
            ]);

        $user =
            $request->user();

        /*
        |--------------------------------------------------------------------------
        | Active Customer
        |--------------------------------------------------------------------------
        */

        if (
            $user->role !==
                'customer' ||
            $user->status !==
                'active'
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your customer account is not active.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Shipping Address
        |--------------------------------------------------------------------------
        */

        $shippingAddress =
            Address::where(
                'id',
                $validated[
                    'shipping_address_id'
                ]
            )
                ->where(
                    'user_id',
                    $user->id
                )
                ->first();

        if (!$shippingAddress) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Shipping address not found.',
            ], 422);
        }

        if (
            !in_array(
                $shippingAddress->type,
                [
                    'shipping',
                    'both',
                ],
                true
            )
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Selected address cannot be used for shipping.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Billing Address
        |--------------------------------------------------------------------------
        */

        $billingAddress =
            $shippingAddress;

        if (
            !empty(
                $validated[
                    'billing_address_id'
                ]
            )
        ) {
            $billingAddress =
                Address::where(
                    'id',
                    $validated[
                        'billing_address_id'
                    ]
                )
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->first();

            if (!$billingAddress) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Billing address not found.',
                ], 422);
            }

            if (
                !in_array(
                    $billingAddress->type,
                    [
                        'billing',
                        'both',
                    ],
                    true
                )
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Selected address cannot be used for billing.',
                ], 422);
            }
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
                    'items.variant.product.primaryImage',
                    'items.variant.size',
                    'items.variant.color',
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

        /*
        |--------------------------------------------------------------------------
        | Database Transaction
        |--------------------------------------------------------------------------
        */

        try {
            $order =
                DB::transaction(
                    function () use (
                        $user,
                        $cart,
                        $validated,
                        $shippingAddress,
                        $billingAddress
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Prepare Cart Items
                        |--------------------------------------------------------------------------
                        */

                        $preparedItems = [];

                        $subtotal = 0;

                        /*
                        |--------------------------------------------------------------------------
                        | Sort Variant IDs
                        |--------------------------------------------------------------------------
                        |
                        | Consistent lock ordering reduces deadlock risk.
                        |
                        */

                        $cartItems =
                            $cart->items
                                ->sortBy(
                                    fn ($item) =>
                                        $item
                                            ->product_variant_id
                                );

                        foreach (
                            $cartItems
                            as $cartItem
                        ) {
                            $variant =
                                $cartItem
                                    ->variant;

                            if (
                                !$variant ||
                                !$variant
                                    ->product
                            ) {
                                throw ValidationException::withMessages([
                                    'cart' =>
                                        'One of the cart products is no longer available.',
                                ]);
                            }

                            if (
                                $variant->status !==
                                    'active' ||
                                $variant->product
                                    ->status !==
                                    'active'
                            ) {
                                throw ValidationException::withMessages([
                                    'cart' =>
                                        "{$variant->product->name} is currently unavailable.",
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Lock Inventory
                            |--------------------------------------------------------------------------
                            */

                            $inventory =
                                Inventory::where(
                                    'product_variant_id',
                                    $variant->id
                                )
                                    ->lockForUpdate()
                                    ->first();

                            if (!$inventory) {
                                throw ValidationException::withMessages([
                                    'cart' =>
                                        "Inventory is unavailable for {$variant->product->name}.",
                                ]);
                            }

                            $availableQuantity =
                                max(
                                    0,

                                    (int)
                                    $inventory
                                        ->quantity

                                    -

                                    (int)
                                    $inventory
                                        ->reserved_quantity
                                );

                            $requestedQuantity =
                                (int)
                                $cartItem
                                    ->quantity;

                            if (
                                $requestedQuantity >
                                $availableQuantity
                            ) {
                                throw ValidationException::withMessages([
                                    'cart' =>
                                        "Only {$availableQuantity} item(s) are available for {$variant->product->name}.",
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Server-Side Price
                            |--------------------------------------------------------------------------
                            */

                            $price =
                                (float)
                                $variant
                                    ->selling_price;

                            $mrp =
                                (float)
                                $variant->mrp;

                            $lineTotal =
                                round(
                                    $price *
                                    $requestedQuantity,
                                    2
                                );

                            $subtotal +=
                                $lineTotal;

                            $preparedItems[] = [
                                'cart_item' =>
                                    $cartItem,

                                'variant' =>
                                    $variant,

                                'inventory' =>
                                    $inventory,

                                'quantity' =>
                                    $requestedQuantity,

                                'price' =>
                                    $price,

                                'mrp' =>
                                    $mrp,

                                'line_total' =>
                                    $lineTotal,
                            ];
                        }

                        $subtotal =
                            round(
                                $subtotal,
                                2
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | Shipping
                        |--------------------------------------------------------------------------
                        */

                        $shippingSetting =
                            ShippingSetting::current();

                        $shippingAmount =
                            $shippingSetting
                                ->calculateShipping(
                                    $subtotal
                                );

                        /*
                        |--------------------------------------------------------------------------
                        | Coupon
                        |--------------------------------------------------------------------------
                        */

                        $coupon = null;

                        $discountAmount = 0;

                        if (
                            !empty(
                                $validated[
                                    'coupon_code'
                                ]
                            )
                        ) {
                            $couponCode =
                                Str::upper(
                                    trim(
                                        $validated[
                                            'coupon_code'
                                        ]
                                    )
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | Lock Coupon
                            |--------------------------------------------------------------------------
                            */

                            $coupon =
                                Coupon::where(
                                    'code',
                                    $couponCode
                                )
                                    ->lockForUpdate()
                                    ->first();

                            if (!$coupon) {
                                throw ValidationException::withMessages([
                                    'coupon_code' =>
                                        'Invalid coupon code.',
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Active
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $coupon->status !==
                                'active'
                            ) {
                                throw ValidationException::withMessages([
                                    'coupon_code' =>
                                        'This coupon is currently inactive.',
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Start Date
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $coupon->starts_at &&
                                now()->lt(
                                    $coupon
                                        ->starts_at
                                )
                            ) {
                                throw ValidationException::withMessages([
                                    'coupon_code' =>
                                        'This coupon is not active yet.',
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Expiry
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $coupon->expires_at &&
                                now()->gt(
                                    $coupon
                                        ->expires_at
                                )
                            ) {
                                throw ValidationException::withMessages([
                                    'coupon_code' =>
                                        'This coupon has expired.',
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Global Usage
                            |--------------------------------------------------------------------------
                            */

                            $globalUsage =
                                CouponUsage::where(
                                    'coupon_id',
                                    $coupon->id
                                )
                                    ->whereHas(
                                        'order',
                                        function (
                                            $query
                                        ) {
                                            $query->where(
                                                'status',
                                                '!=',
                                                'cancelled'
                                            );
                                        }
                                    )
                                    ->count();

                            if (
                                $coupon
                                    ->usage_limit !==
                                    null
                                &&
                                $globalUsage >=
                                    $coupon
                                        ->usage_limit
                            ) {
                                throw ValidationException::withMessages([
                                    'coupon_code' =>
                                        'This coupon has reached its usage limit.',
                                ]);
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
                                        function (
                                            $query
                                        ) {
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
                                $coupon
                                    ->per_user_limit
                            ) {
                                throw ValidationException::withMessages([
                                    'coupon_code' =>
                                        'You have already used this coupon the maximum number of times.',
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Minimum Amount
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $subtotal <
                                (float)
                                $coupon
                                    ->minimum_order_amount
                            ) {
                                throw ValidationException::withMessages([
                                    'coupon_code' =>
                                        'Minimum order value of ₹' .
                                        number_format(
                                            (float)
                                            $coupon
                                                ->minimum_order_amount,
                                            0
                                        ) .
                                        ' is required for this coupon.',
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Discount
                            |--------------------------------------------------------------------------
                            */

                            $discountAmount =
                                $coupon
                                    ->calculateDiscount(
                                        $subtotal
                                    );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Total
                        |--------------------------------------------------------------------------
                        */

                        $totalAmount =
                            max(
                                0,

                                $subtotal
                                +
                                $shippingAmount
                                -
                                $discountAmount
                            );

                        $totalAmount =
                            round(
                                $totalAmount,
                                2
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | Address Snapshots
                        |--------------------------------------------------------------------------
                        */

                        $shippingSnapshot = [
                            'full_name' =>
                                $shippingAddress
                                    ->full_name,

                            'phone' =>
                                $shippingAddress
                                    ->phone,

                            'address_line_1' =>
                                $shippingAddress
                                    ->address_line_1,

                            'address_line_2' =>
                                $shippingAddress
                                    ->address_line_2,

                            'landmark' =>
                                $shippingAddress
                                    ->landmark,

                            'city' =>
                                $shippingAddress
                                    ->city,

                            'state' =>
                                $shippingAddress
                                    ->state,

                            'postal_code' =>
                                $shippingAddress
                                    ->postal_code,

                            'country' =>
                                $shippingAddress
                                    ->country,
                        ];

                        $billingSnapshot = [
                            'full_name' =>
                                $billingAddress
                                    ->full_name,

                            'phone' =>
                                $billingAddress
                                    ->phone,

                            'address_line_1' =>
                                $billingAddress
                                    ->address_line_1,

                            'address_line_2' =>
                                $billingAddress
                                    ->address_line_2,

                            'landmark' =>
                                $billingAddress
                                    ->landmark,

                            'city' =>
                                $billingAddress
                                    ->city,

                            'state' =>
                                $billingAddress
                                    ->state,

                            'postal_code' =>
                                $billingAddress
                                    ->postal_code,

                            'country' =>
                                $billingAddress
                                    ->country,
                        ];

                        /*
                        |--------------------------------------------------------------------------
                        | Unique Order Number
                        |--------------------------------------------------------------------------
                        */

                        do {
                            $orderNumber =
                                'BM-' .
                                now()->format(
                                    'Ymd'
                                ) .
                                '-' .
                                Str::upper(
                                    Str::random(6)
                                );
                        } while (
                            Order::where(
                                'order_number',
                                $orderNumber
                            )->exists()
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | Create Order
                        |--------------------------------------------------------------------------
                        */

                        $order =
                            Order::create([
                                'user_id' =>
                                    $user->id,

                                'order_number' =>
                                    $orderNumber,

                                'status' =>
                                    'pending',

                                'payment_method' =>
                                    $validated[
                                        'payment_method'
                                    ],

                                'payment_status' =>
                                    'pending',

                                'subtotal' =>
                                    $subtotal,

                                'shipping_amount' =>
                                    $shippingAmount,

                                'discount_amount' =>
                                    $discountAmount,

                                'coupon_id' =>
                                    $coupon?->id,

                                'coupon_code' =>
                                    $coupon?->code,

                                'total_amount' =>
                                    $totalAmount,

                                'shipping_address' =>
                                    $shippingSnapshot,

                                'billing_address' =>
                                    $billingSnapshot,

                                'customer_note' =>
                                    $validated[
                                        'customer_note'
                                    ]
                                    ??
                                    null,
                            ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Create Order Items
                        |--------------------------------------------------------------------------
                        */

                        foreach (
                            $preparedItems
                            as $prepared
                        ) {
                            $variant =
                                $prepared[
                                    'variant'
                                ];

                            $inventory =
                                $prepared[
                                    'inventory'
                                ];

                            $quantity =
                                $prepared[
                                    'quantity'
                                ];

                            $product =
                                $variant
                                    ->product;

                            /*
                            |--------------------------------------------------------------------------
                            | Order Item Snapshot
                            |--------------------------------------------------------------------------
                            */

                            OrderItem::create([
                                'order_id' =>
                                    $order->id,

                                'product_id' =>
                                    $product->id,

                                'product_variant_id' =>
                                    $variant->id,

                                'product_name' =>
                                    $product->name,

                                'variant_sku' =>
                                    $variant->sku,

                                'size_name' =>
                                    $variant
                                        ->size
                                        ? (
                                            $variant
                                                ->size
                                                ->display_name
                                            ??
                                            $variant
                                                ->size
                                                ->name
                                        )
                                        : null,

                                'color_name' =>
                                    $variant
                                        ->color
                                        ? (
                                            $variant
                                                ->color
                                                ->display_name
                                            ??
                                            $variant
                                                ->color
                                                ->name
                                        )
                                        : null,

                                'image' =>
                                    $product
                                        ->primaryImage
                                        ? $product
                                            ->primaryImage
                                            ->image
                                        : null,

                                'mrp' =>
                                    $prepared[
                                        'mrp'
                                    ],

                                'price' =>
                                    $prepared[
                                        'price'
                                    ],

                                'quantity' =>
                                    $quantity,

                                'line_total' =>
                                    $prepared[
                                        'line_total'
                                    ],
                            ]);

                            /*
                            |--------------------------------------------------------------------------
                            | Reserve Inventory
                            |--------------------------------------------------------------------------
                            */

                            $beforeAvailable =
                                max(
                                    0,

                                    (int)
                                    $inventory
                                        ->quantity

                                    -

                                    (int)
                                    $inventory
                                        ->reserved_quantity
                                );

                            $inventory->update([
                                'reserved_quantity' =>
                                    (int)
                                    $inventory
                                        ->reserved_quantity

                                    +

                                    $quantity,
                            ]);

                            $afterAvailable =
                                max(
                                    0,

                                    (int)
                                    $inventory
                                        ->quantity

                                    -

                                    (int)
                                    $inventory
                                        ->reserved_quantity
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | Inventory History
                            |--------------------------------------------------------------------------
                            */

                            InventoryMovement::create([
                                'product_variant_id' =>
                                    $variant->id,

                                'user_id' =>
                                    $user->id,

                                'type' =>
                                    'order_reserved',

                                'quantity' =>
                                    -$quantity,

                                'before_quantity' =>
                                    $beforeAvailable,

                                'after_quantity' =>
                                    $afterAvailable,

                                'reference_type' =>
                                    'order',

                                'reference_id' =>
                                    $order->id,

                                'notes' =>
                                    "Stock reserved for order {$order->order_number}",
                            ]);
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Coupon Usage
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $coupon &&
                            $discountAmount > 0
                        ) {
                            CouponUsage::create([
                                'coupon_id' =>
                                    $coupon->id,

                                'user_id' =>
                                    $user->id,

                                'order_id' =>
                                    $order->id,

                                'discount_amount' =>
                                    $discountAmount,
                            ]);
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Clear Cart
                        |--------------------------------------------------------------------------
                        */

                        $cart
                            ->items()
                            ->delete();

                        return $order;
                    },
                    3
                );
        } catch (
            ValidationException $exception
        ) {
            throw $exception;
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        $order->load([
            'items',
            'coupon',
            'user',
            'invoice',
        ]);

        try {
            app(InvoicePdfService::class)->generate($order);
            $order->load('invoice');

            if ($order->user?->email) {
                $emailService = app(OrderEmailService::class);
                app()->terminating(function () use ($emailService, $order) {
                    try {
                        $emailService->sendConfirmation($order);
                    } catch (\Throwable $mailException) {
                        logger()->warning('Order confirmation email could not be sent with PHP mail().', [
                            'order_id' => $order->id,
                            'error' => $mailException->getMessage(),
                        ]);
                    }
                });
            }
        } catch (\Throwable $invoiceException) {
            logger()->warning('Invoice could not be prepared after order placement.', [
                'order_id' => $order->id,
                'error' => $invoiceException->getMessage(),
            ]);
        }

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Order placed successfully.',

            'data' =>
                $order,
        ], 201);
    }
}