<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with([
            'category',
            'primaryImage',
        ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })

            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })

            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })

            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'nullable',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:100',
                'unique:products,sku',
            ],

            'short_description' => [
                'nullable',
                'string',
            ],

            'description' => [
                'nullable',
                'string',
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

            'set_quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'featured' => [
                'nullable',
                'boolean',
            ],

            'best_seller' => [
                'nullable',
                'boolean',
            ],

            'new_arrival' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            'seo_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'seo_description' => [
                'nullable',
                'string',
            ],
        ]);

        $baseSlug = Str::slug($validated['name']);

        if (!$baseSlug) {
            $baseSlug = 'product';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $product = Product::create([
            'category_id' => $validated['category_id'] ?? null,

            'name' => $validated['name'],

            'slug' => $slug,

            'sku' => $validated['sku'] ?? null,

            'short_description' =>
                $validated['short_description'] ?? null,

            'description' =>
                $validated['description'] ?? null,

            'mrp' => $validated['mrp'],

            'selling_price' =>
                $validated['selling_price'],

            'set_quantity' =>
                $validated['set_quantity'] ?? 1,

            'featured' =>
                $validated['featured'] ?? false,

            'best_seller' =>
                $validated['best_seller'] ?? false,

            'new_arrival' =>
                $validated['new_arrival'] ?? false,

            'status' => $validated['status'],

            'seo_title' =>
                $validated['seo_title'] ?? null,

            'seo_description' =>
                $validated['seo_description'] ?? null,
        ]);

        $product->load([
            'category',
            'images',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'images',
            'variants.size',
            'variants.color',
            'variants.inventory',
        ]);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    public function update(
        Request $request,
        Product $product
    ) {
        $validated = $request->validate([
            'category_id' => [
                'nullable',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique(
                    'products',
                    'sku'
                )->ignore($product->id),
            ],

            'short_description' => [
                'nullable',
                'string',
            ],

            'description' => [
                'nullable',
                'string',
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

            'set_quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'featured' => [
                'nullable',
                'boolean',
            ],

            'best_seller' => [
                'nullable',
                'boolean',
            ],

            'new_arrival' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            'seo_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'seo_description' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update slug if product name changes
        |--------------------------------------------------------------------------
        */

        if ($product->name !== $validated['name']) {
            $baseSlug = Str::slug(
                $validated['name']
            );

            if (!$baseSlug) {
                $baseSlug = 'product';
            }

            $slug = $baseSlug;
            $counter = 1;

            while (
                Product::where('slug', $slug)
                    ->where(
                        'id',
                        '!=',
                        $product->id
                    )
                    ->exists()
            ) {
                $slug =
                    $baseSlug .
                    '-' .
                    $counter;

                $counter++;
            }

            $product->slug = $slug;
        }

        $product->category_id =
            $validated['category_id'] ?? null;

        $product->name =
            $validated['name'];

        $product->sku =
            $validated['sku'] ?? null;

        $product->short_description =
            $validated['short_description'] ?? null;

        $product->description =
            $validated['description'] ?? null;

        $product->mrp =
            $validated['mrp'];

        $product->selling_price =
            $validated['selling_price'];

        $product->set_quantity =
            $validated['set_quantity'] ?? 1;

        $product->featured =
            $validated['featured'] ?? false;

        $product->best_seller =
            $validated['best_seller'] ?? false;

        $product->new_arrival =
            $validated['new_arrival'] ?? false;

        $product->status =
            $validated['status'];

        $product->seo_title =
            $validated['seo_title'] ?? null;

        $product->seo_description =
            $validated['seo_description'] ?? null;

        $product->save();

        $product->load([
            'category',
            'images',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }
}