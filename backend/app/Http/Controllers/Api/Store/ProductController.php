<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Product Listing
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Product::query()
            ->where('status', 'active')
            ->with([
                'category',
                'primaryImage',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'short_description',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'sku',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Category Filter
        |--------------------------------------------------------------------------
        */

        /*
        * 1. category_id
        *
        * Used by the new URL structure:
        *
        * /shop/meenakari-bangles/designer-meenakari
        *
        * The frontend sends:
        *
        * /store/products?category_id=26
        *
        * When category_id is provided, we MUST filter
        * exactly by that category.
        */
        if ($request->filled('category_id')) {

            $categoryId = (int) $request->category_id;

            $category = Category::where('id', $categoryId)
                ->where('status', 'active')
                ->first();

            if (!$category) {

                $query->whereRaw('1 = 0');

            } elseif ($category->parent_id === null) {

                /*
                |--------------------------------------------------------------------------
                | PARENT CATEGORY
                |--------------------------------------------------------------------------
                |
                | Example:
                |
                | Glass Bangles = 11
                |
                | Get ALL products belonging to:
                |
                | Glass Bangles
                | + all direct children
                |
                */

                $categoryIds = Category::where('status', 'active')
                    ->where(function ($q) use ($categoryId) {
                        $q->where('id', $categoryId)
                        ->orWhere('parent_id', $categoryId);
                    })
                    ->pluck('id');

                $query->whereIn(
                    'category_id',
                    $categoryIds
                );

            } else {

                /*
                |--------------------------------------------------------------------------
                | SUBCATEGORY
                |--------------------------------------------------------------------------
                |
                | Example:
                |
                | Bridal Glass Bangles = 27
                |
                | Get ALL products assigned to category 27.
                |
                */

                $query->where(
                    'category_id',
                    $category->id
                );
            }
        }

        /*
        * 2. Legacy category slug filter
        *
        * Used when frontend sends:
        *
        * /store/products?category=meenakari-bangles
        *
        * Parent category:
        * show products belonging to the parent OR
        * its direct children.
        */
        elseif ($request->filled('category')) {

            $category = trim(
                (string) $request->category
            );

            $categoryRecord = Category::where(
                'slug',
                $category
            )
                ->where(
                    'status',
                    'active'
                )
                ->first([
                    'id',
                    'parent_id'
                ]);

            /*
            * Backward compatibility for /shop?category=bridal
            */
            if (
                !$categoryRecord &&
                $category === 'bridal'
            ) {

                $categoryRecord = Category::where(
                    'status',
                    'active'
                )
                    ->whereNull('parent_id')
                    ->where(function ($q) {
                        $q->where(
                            'slug',
                            'like',
                            '%bridal%'
                        )
                        ->orWhere(
                            'name',
                            'like',
                            '%bridal%'
                        );
                    })
                    ->first([
                        'id',
                        'parent_id'
                    ]);
            }

            if ($categoryRecord) {

                $query->whereHas(
                    'category',
                    function ($q) use ($categoryRecord) {

                        $q->where(
                            'id',
                            $categoryRecord->id
                        );

                        /*
                        * If this is a parent category,
                        * also include its direct children.
                        */
                        if (
                            $categoryRecord->parent_id === null
                        ) {
                            $q->orWhere(
                                'parent_id',
                                $categoryRecord->id
                            );
                        }
                    }
                );

            } elseif ($category === 'bangles') {

                $query->whereHas(
                    'category',
                    function ($q) {
                        $q->whereNull('parent_id');
                    }
                );

            } else {

                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('min_price')) {

            $query->where(
                'selling_price',
                '>=',
                (float) $request->min_price
            );
        }

        if ($request->filled('max_price')) {

            $query->where(
                'selling_price',
                '<=',
                (float) $request->max_price
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Labels
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('featured')) {
            $query->where(
                'featured',
                true
            );
        }

        if ($request->boolean('best_seller')) {
            $query->where(
                'best_seller',
                true
            );
        }

        if ($request->boolean('new_arrival')) {
            $query->where(
                'new_arrival',
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        switch ($request->sort) {
            case 'price_low':
                $query->orderBy(
                    'selling_price',
                    'asc'
                );
                break;

            case 'price_high':
                $query->orderBy(
                    'selling_price',
                    'desc'
                );
                break;

            case 'name':
                $query->orderBy(
                    'name',
                    'asc'
                );
                break;

            default:
                $query->latest();
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->paginate((int) $request->input('per_page', 12));

        $products->getCollection()->transform(function ($product) {
            $product->setAttribute(
                'image',
                $product->primaryImage?->image
                    ? '/storage/' . ltrim($product->primaryImage->image, '/')
                    : null
            );
            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Product Detail
    |--------------------------------------------------------------------------
    */

    public function show(string $slug)
    {
        $product = Product::where(
            'slug',
            $slug
        )
            ->where(
                'status',
                'active'
            )
            ->with([
                'category',

                'images' => function ($query) {
                    $query
                        ->orderByDesc('is_primary')
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },

                'variants' => function ($query) {
                    $query
                        ->where(
                            'status',
                            'active'
                        )
                        ->orderBy('id');
                },

                'variants.size',
                'variants.color',
                'variants.inventory',
                'reviews' => function ($query) {
                    $query->where('status', 'approved')
                        ->with('user:id,name')
                        ->latest();
                },
            ])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Product not found.',
            ], 404);
        }

        $approvedReviews = $product->reviews;
        $reviewCount = $approvedReviews->count();
        $reviewAverage = $reviewCount > 0
            ? round($approvedReviews->avg('rating'), 1)
            : 0;

        $product->setAttribute('review_count', $reviewCount);
        $product->setAttribute('review_average', $reviewAverage);
        $product->setAttribute(
            'image',
            $product->primaryImage?->image
                ? '/storage/' . ltrim($product->primaryImage->image, '/')
                : null
        );

        $recommendedQuery = Product::query()
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->with('primaryImage')
            ->latest();

        if ($product->category_id) {
            $recommendedQuery->where('category_id', $product->category_id);
        }

        $recommended = $recommendedQuery->take(4)->get();

        if ($recommended->isEmpty()) {
            $recommended = Product::query()
                ->where('status', 'active')
                ->where('id', '!=', $product->id)
                ->with('primaryImage')
                ->latest()
                ->take(4)
                ->get();
        }

        $recommended->transform(function ($item) {
            $item->setAttribute(
                'image',
                $item->primaryImage?->image
                    ? '/storage/' . ltrim($item->primaryImage->image, '/')
                    : null
            );
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $product,
            'recommended' => $recommended,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Public Categories
    |--------------------------------------------------------------------------
    */

    public function categories()
    {
        $categories = Category::where('status', 'active')
            ->whereNull('parent_id')
            ->with([
                'children' => function ($query) {
                    $query->where('status', 'active')
                        ->select(['id', 'parent_id', 'name', 'slug', 'image', 'sort_order']);
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'name',
                'slug',
                'image',
                'sort_order',
            ]);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}