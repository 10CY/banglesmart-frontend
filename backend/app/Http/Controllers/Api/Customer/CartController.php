<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Get Customer Cart
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        return $this->cartResponse($cart);
    }

    /*
    |--------------------------------------------------------------------------
    | Add Item
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => [
                'nullable',
                'integer',
                'exists:product_variants,id',
                'required_without:product_id',
            ],
            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
                'required_without:product_variant_id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        if (!empty($validated['product_variant_id'])) {
            $variant = ProductVariant::with(['product', 'inventory'])
                ->findOrFail($validated['product_variant_id']);
        } else {
            $variant = ProductVariant::with(['product', 'inventory'])
                ->where('product_id', $validated['product_id'])
                ->where('status', 'active')
                ->whereHas('inventory')
                ->orderBy('id')
                ->first();

            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'This product does not have an available variant yet.',
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Product / Variant must be active
        |--------------------------------------------------------------------------
        */

        if (
            $variant->status !== 'active' ||
            !$variant->product ||
            $variant->product->status !== 'active'
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This product variant is currently unavailable.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        if (!$variant->inventory) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Inventory is not available for this variant.',
            ], 422);
        }

        $availableQuantity = max(
            0,
            $variant->inventory->quantity -
            $variant->inventory->reserved_quantity
        );

        if ($availableQuantity <= 0) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This product is currently out of stock.',
            ], 422);
        }

        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $existingItem = CartItem::where(
            'cart_id',
            $cart->id
        )
            ->where(
                'product_variant_id',
                $variant->id
            )
            ->first();

        $newQuantity =
            ($existingItem?->quantity ?? 0)
            +
            $validated['quantity'];

        if ($newQuantity > $availableQuantity) {
            return response()->json([
                'success' => false,

                'message' =>
                    "Only {$availableQuantity} item(s) are currently available.",
            ], 422);
        }

        if ($existingItem) {
            $existingItem->update([
                'quantity' =>
                    $newQuantity,
            ]);
        } else {
            CartItem::create([
                'cart_id' =>
                    $cart->id,

                'product_variant_id' =>
                    $variant->id,

                'quantity' =>
                    $validated['quantity'],
            ]);
        }

        return $this->cartResponse(
            $cart,
            'Product added to cart successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Quantity
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        CartItem $cartItem
    ) {
        $this->ensureOwnership(
            $request,
            $cartItem
        );

        $validated =
            $request->validate([
                'quantity' => [
                    'required',
                    'integer',
                    'min:1',
                ],
            ]);

        $cartItem->load([
            'variant.inventory',
            'variant.product',
        ]);

        $variant =
            $cartItem->variant;

        if (
            !$variant ||
            !$variant->inventory
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This product is no longer available.',
            ], 422);
        }

        if (
            $variant->status !== 'active' ||
            $variant->product?->status !== 'active'
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This product variant is currently unavailable.',
            ], 422);
        }

        $availableQuantity =
            max(
                0,
                $variant
                    ->inventory
                    ->quantity
                -
                $variant
                    ->inventory
                    ->reserved_quantity
            );

        if (
            $validated['quantity']
            >
            $availableQuantity
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    "Only {$availableQuantity} item(s) are currently available.",
            ], 422);
        }

        $cartItem->update([
            'quantity' =>
                $validated['quantity'],
        ]);

        return $this->cartResponse(
            $cartItem->cart,
            'Cart updated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Remove Item
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        CartItem $cartItem
    ) {
        $this->ensureOwnership(
            $request,
            $cartItem
        );

        $cart =
            $cartItem->cart;

        $cartItem->delete();

        return $this->cartResponse(
            $cart,
            'Item removed from cart.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Clear Cart
    |--------------------------------------------------------------------------
    */

    public function clear(
        Request $request
    ) {
        $cart =
            Cart::firstOrCreate([
                'user_id' =>
                    $request->user()->id,
            ]);

        $cart->items()->delete();

        return $this->cartResponse(
            $cart,
            'Cart cleared successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Ownership Protection
    |--------------------------------------------------------------------------
    */

    private function ensureOwnership(
        Request $request,
        CartItem $cartItem
    ): void {
        $cartItem->loadMissing('cart');

        if (
            !$cartItem->cart ||
            (int) $cartItem->cart->user_id !==
            (int) $request->user()->id
        ) {
            abort(
                404,
                'Cart item not found.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cart Response
    |--------------------------------------------------------------------------
    */

    private function cartResponse(
        Cart $cart,
        ?string $message = null
    ) {
        $cart->load([
            'items.variant.product.primaryImage',
            'items.variant.size',
            'items.variant.color',
            'items.variant.inventory',
        ]);

        $items =
            $cart->items->map(
                function ($item) {
                    $variant =
                        $item->variant;

                    if (!$variant) {
                        return null;
                    }

                    $price =
                        (float)
                        $variant
                            ->selling_price;

                    $mrp =
                        (float)
                        $variant->mrp;

                    $quantity =
                        (int)
                        $item->quantity;

                    $available =
                        $variant->inventory
                            ? max(
                                0,

                                $variant
                                    ->inventory
                                    ->quantity

                                -

                                $variant
                                    ->inventory
                                    ->reserved_quantity
                            )
                            : 0;

                    return [
                        'id' =>
                            $item->id,

                        'quantity' =>
                            $quantity,

                        'line_total' =>
                            round(
                                $price *
                                $quantity,
                                2
                            ),

                        'variant' => [
                            'id' =>
                                $variant->id,

                            'sku' =>
                                $variant->sku,

                            'mrp' =>
                                $mrp,

                            'selling_price' =>
                                $price,

                            'status' =>
                                $variant->status,

                            'available_quantity' =>
                                $available,

                            'product' =>
                                $variant->product,

                            'size' =>
                                $variant->size,

                            'color' =>
                                $variant->color,
                        ],
                    ];
                }
            )
            ->filter()
            ->values();

        $subtotal =
            $items->sum(
                'line_total'
            );

        $itemCount =
            $items->sum(
                'quantity'
            );

        return response()->json([
            'success' => true,

            'message' =>
                $message,

            'data' => [
                'id' =>
                    $cart->id,

                'items' =>
                    $items,

                'item_count' =>
                    $itemCount,

                'subtotal' =>
                    round(
                        $subtotal,
                        2
                    ),
            ],
        ]);
    }
}