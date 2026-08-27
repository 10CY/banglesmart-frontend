<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with([
            'product:id,name,slug',
            'user:id,name,email',
        ])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    public function update(Request $request, Review $review)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $review->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Review status updated successfully.',
            'data' => $review->fresh(['product', 'user']),
        ]);
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }
}
