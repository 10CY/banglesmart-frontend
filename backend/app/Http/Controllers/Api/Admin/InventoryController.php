<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
class InventoryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Inventory List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Inventory::query()
            ->with([
                'variant.product.primaryImage',
                'variant.size',
                'variant.color',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search Product / SKU
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->whereHas(
                'variant',
                function ($variantQuery) use ($search) {

                    $variantQuery
                        ->where(
                            'sku',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'product',
                            function ($productQuery) use ($search) {

                                $productQuery
                                    ->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'sku',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Stock Filter
        |--------------------------------------------------------------------------
        */

        if ($request->stock_status === 'out') {

            $query->whereRaw(
                '(quantity - reserved_quantity) <= 0'
            );

        } elseif ($request->stock_status === 'low') {

            $query
                ->whereRaw(
                    '(quantity - reserved_quantity) > 0'
                )
                ->whereRaw(
                    '(quantity - reserved_quantity) <= low_stock_limit'
                );

        } elseif ($request->stock_status === 'in_stock') {

            $query->whereRaw(
                '(quantity - reserved_quantity) > low_stock_limit'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Variant Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $status = $request->status;

            $query->whereHas(
                'variant',
                function ($variantQuery) use ($status) {
                    $variantQuery->where(
                        'status',
                        $status
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $inventories = $query
            ->latest('id')
            ->paginate(25);

        /*
        |--------------------------------------------------------------------------
        | Dashboard Summary
        |--------------------------------------------------------------------------
        */

        $totalVariants =
            Inventory::count();

        $outOfStock =
            Inventory::whereRaw(
                '(quantity - reserved_quantity) <= 0'
            )->count();

        $lowStock =
            Inventory::whereRaw(
                '(quantity - reserved_quantity) > 0'
            )
                ->whereRaw(
                    '(quantity - reserved_quantity) <= low_stock_limit'
                )
                ->count();

        $availableUnits =
            Inventory::sum('quantity')
            -
            Inventory::sum('reserved_quantity');

        return response()->json([
            'success' => true,

            'data' => $inventories,

            'summary' => [
                'total_variants' =>
                    $totalVariants,

                'available_units' =>
                    max(
                        0,
                        $availableUnits
                    ),

                'low_stock' =>
                    $lowStock,

                'out_of_stock' =>
                    $outOfStock,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Inventory
    |--------------------------------------------------------------------------
    */

    public function update(
    Request $request,
    Inventory $inventory
) {
    $validated = $request->validate([
        'quantity' => [
            'required',
            'integer',
            'min:0',
        ],

        'low_stock_limit' => [
            'required',
            'integer',
            'min:0',
        ],

        'notes' => [
            'nullable',
            'string',
            'max:1000',
        ],
    ]);

    if (
        $validated['quantity']
        <
        $inventory->reserved_quantity
    ) {
        return response()->json([
            'success' => false,

            'message' =>
                'Quantity cannot be lower than reserved stock.',
        ], 422);
    }

    DB::transaction(function () use (
        $inventory,
        $validated,
        $request
    ) {
        $beforeQuantity =
            $inventory->quantity;

        $afterQuantity =
            $validated['quantity'];

        $change =
            $afterQuantity -
            $beforeQuantity;

        $inventory->update([
            'quantity' =>
                $afterQuantity,

            'low_stock_limit' =>
                $validated['low_stock_limit'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create history only when stock quantity actually changes
        |--------------------------------------------------------------------------
        */

        if ($change !== 0) {
            InventoryMovement::create([
                'product_variant_id' =>
                    $inventory->product_variant_id,

                'user_id' =>
                    $request->user()?->id,

                'type' =>
                    'adjustment',

                'quantity' =>
                    $change,

                'before_quantity' =>
                    $beforeQuantity,

                'after_quantity' =>
                    $afterQuantity,

                'notes' =>
                    $validated['notes']
                    ?? 'Manual inventory adjustment',
            ]);
        }
    });

    $inventory->refresh();

    $inventory->load([
        'variant.product.primaryImage',
        'variant.size',
        'variant.color',
    ]);

    return response()->json([
        'success' => true,

        'message' =>
            'Inventory updated successfully.',

        'data' =>
            $inventory,
    ]);
}
}