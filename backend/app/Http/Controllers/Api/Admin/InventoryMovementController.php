<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryMovement::query()
            ->with([
                'variant.product.primaryImage',
                'variant.size',
                'variant.color',
                'user',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search =
                $request->search;

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
                                $productQuery->where(
                                    'name',
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
        | Type Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        $movements =
            $query
                ->latest()
                ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $movements,
        ]);
    }
}