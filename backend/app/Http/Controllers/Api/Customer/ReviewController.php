<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:150'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = Order::where('user_id', $request->user()->id)
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->latest('id')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'You can review this product after it has been delivered to you.',
            ], 422);
        }

        $review = Review::updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
            ],
            [
                'order_id' => $order->id,
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'comment' => $validated['comment'] ?? null,
                'status' => 'pending',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you. Your review has been submitted for approval.',
            'data' => $review,
        ], 201);
    }

    public function mine(Request $request, Product $product)
    {
        $review = Review::where('product_id', $product->id)
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json([
            'success' => true,
            'data' => $review,
        ]);
    }
}
