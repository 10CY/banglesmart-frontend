<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Wishlist
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $wishlist = Wishlist::firstOrCreate([
            'user_id' =>
                $request->user()->id,
        ]);

        $wishlist->load([
            'items.product.category',
            'items.product.primaryImage',
        ]);

        $items =
            $wishlist->items
                ->filter(
                    fn ($item) =>
                        $item->product !== null
                )
                ->values();

        return response()->json([
            'success' => true,

            'data' => [
                'id' =>
                    $wishlist->id,

                'items' =>
                    $items,

                'item_count' =>
                    $items->count(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Add Product
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated =
            $request->validate([
                'product_id' => [
                    'required',
                    'integer',
                    'exists:products,id',
                ],
            ]);

        $product =
            Product::where(
                'id',
                $validated['product_id']
            )
                ->where(
                    'status',
                    'active'
                )
                ->first();

        if (!$product) {
            return response()->json([
                'success' => false,

                'message' =>
                    'This product is currently unavailable.',
            ], 422);
        }

        $wishlist =
            Wishlist::firstOrCreate([
                'user_id' =>
                    $request->user()->id,
            ]);

        $item =
            WishlistItem::firstOrCreate([
                'wishlist_id' =>
                    $wishlist->id,

                'product_id' =>
                    $product->id,
            ]);

        $item->load([
            'product.category',
            'product.primaryImage',
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                $item->wasRecentlyCreated
                    ? 'Product added to wishlist.'
                    : 'Product is already in your wishlist.',

            'data' =>
                $item,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Remove Product
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        WishlistItem $wishlistItem
    ) {
        $wishlistItem->loadMissing(
            'wishlist'
        );

        if (
            !$wishlistItem->wishlist ||
            (int)
            $wishlistItem
                ->wishlist
                ->user_id !==
            (int)
            $request->user()->id
        ) {
            abort(
                404,
                'Wishlist item not found.'
            );
        }

        $wishlistItem->delete();

        return response()->json([
            'success' => true,

            'message' =>
                'Product removed from wishlist.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Check Product
    |--------------------------------------------------------------------------
    */

    public function check(
        Request $request,
        Product $product
    ) {
        $wishlist =
            Wishlist::where(
                'user_id',
                $request->user()->id
            )->first();

        if (!$wishlist) {
            return response()->json([
                'success' => true,

                'data' => [
                    'wishlisted' =>
                        false,

                    'wishlist_item_id' =>
                        null,
                ],
            ]);
        }

        $item =
            WishlistItem::where(
                'wishlist_id',
                $wishlist->id
            )
                ->where(
                    'product_id',
                    $product->id
                )
                ->first();

        return response()->json([
            'success' => true,

            'data' => [
                'wishlisted' =>
                    $item !== null,

                'wishlist_item_id' =>
                    $item?->id,
            ],
        ]);
    }
}