<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * List approved reviews for a product.
     */
    public function index(int $productId): JsonResponse
    {
        $reviews = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->with('user')
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'comment'    => $r->comment,
                'user_name'  => $r->user->name,
                'created_at' => $r->created_at->toDateString(),
            ]);

        $avg = round($reviews->avg('rating'), 1);

        return response()->json([
            'data'       => $reviews,
            'avg_rating' => $avg ?: 0,
            'count'      => $reviews->count(),
        ]);
    }

    /**
     * Submit a review for a product.
     * User must have purchased this product (optional order association).
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $product = Product::where('is_active', true)->findOrFail($productId);

        $validated = $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string|max:1000',
            'order_id' => 'nullable|integer|exists:orders,id',
        ]);

        $userId = $request->user()->id;

        // Prevent duplicate review per user per product
        $existing = Review::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        $review = Review::create([
            'user_id'     => $userId,
            'product_id'  => $product->id,
            'order_id'    => $validated['order_id'] ?? null,
            'rating'      => $validated['rating'],
            'comment'     => $validated['comment'] ?? null,
            'is_approved' => false, // Admin must approve
        ]);

        return response()->json([
            'message' => 'Review submitted. It will appear after approval.',
            'data'    => [
                'id'      => $review->id,
                'rating'  => $review->rating,
                'comment' => $review->comment,
            ],
        ], 201);
    }
}
