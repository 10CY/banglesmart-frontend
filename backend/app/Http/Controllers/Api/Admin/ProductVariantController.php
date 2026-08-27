<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List Product Variants
    |--------------------------------------------------------------------------
    */

    public function index(Product $product)
    {
        $variants = $product->variants()
            ->with([
                'size',
                'color',
                'inventory',
            ])
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $variants,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Variant
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        Product $product
    ) {
        $validated = $request->validate([
            'size_id' => [
                'required',
                'exists:sizes,id',
            ],

            'color_id' => [
                'required',
                'exists:colors,id',
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:product_variants,sku',
            ],

            'mrp' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'low_stock_limit' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $alreadyExists = ProductVariant::where(
            'product_id',
            $product->id
        )
            ->where(
                'size_id',
                $validated['size_id']
            )
            ->where(
                'color_id',
                $validated['color_id']
            )
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This size and color combination already exists.',
            ], 422);
        }

        $variant = DB::transaction(
            function () use (
                $validated,
                $product
            ) {
                $variant =
                    ProductVariant::create([
                        'product_id' =>
                            $product->id,

                        'size_id' =>
                            $validated['size_id'],

                        'color_id' =>
                            $validated['color_id'],

                        'sku' =>
                            $validated['sku'],

                        'mrp' =>
                            $validated['mrp'],

                        'selling_price' =>
                            $validated[
                                'selling_price'
                            ],

                        'status' =>
                            $validated['status'],
                    ]);

                $variant->inventory()->create([
                    'quantity' =>
                        $validated['quantity'],

                    'reserved_quantity' => 0,

                    'low_stock_limit' =>
                        $validated[
                            'low_stock_limit'
                        ] ?? 5,
                ]);

                return $variant;
            }
        );

        $variant->load([
            'size',
            'color',
            'inventory',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Variant created successfully.',
            'data' => $variant,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Variant
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        ProductVariant $variant
    ) {
        $validated = $request->validate([
            'size_id' => [
                'required',
                'exists:sizes,id',
            ],

            'color_id' => [
                'required',
                'exists:colors,id',
            ],

            'sku' => [
                'required',
                'string',
                'max:100',

                Rule::unique(
                    'product_variants',
                    'sku'
                )->ignore($variant->id),
            ],

            'mrp' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'low_stock_limit' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $duplicate =
            ProductVariant::where(
                'product_id',
                $variant->product_id
            )
                ->where(
                    'size_id',
                    $validated['size_id']
                )
                ->where(
                    'color_id',
                    $validated['color_id']
                )
                ->where(
                    'id',
                    '!=',
                    $variant->id
                )
                ->exists();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This size and color combination already exists.',
            ], 422);
        }

        DB::transaction(
            function () use (
                $variant,
                $validated
            ) {
                $variant->update([
                    'size_id' =>
                        $validated['size_id'],

                    'color_id' =>
                        $validated['color_id'],

                    'sku' =>
                        $validated['sku'],

                    'mrp' =>
                        $validated['mrp'],

                    'selling_price' =>
                        $validated[
                            'selling_price'
                        ],

                    'status' =>
                        $validated['status'],
                ]);

                $variant->inventory()
                    ->updateOrCreate(
                        [
                            'product_variant_id' =>
                                $variant->id,
                        ],
                        [
                            'quantity' =>
                                $validated[
                                    'quantity'
                                ],

                            'low_stock_limit' =>
                                $validated[
                                    'low_stock_limit'
                                ] ?? 5,
                        ]
                    );
            }
        );

        $variant->load([
            'size',
            'color',
            'inventory',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Variant updated successfully.',
            'data' => $variant,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Variant
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ProductVariant $variant
    ) {
        $variant->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'Variant deleted successfully.',
        ]);
    }
}