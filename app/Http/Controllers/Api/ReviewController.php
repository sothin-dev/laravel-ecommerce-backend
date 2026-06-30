<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ReviewController extends Controller
{
    #[
        OA\Get(
            path: '/api/products/{productId}/reviews',
            summary: 'List approved reviews for a product',
            tags: ['Reviews'],
            parameters: [
                new OA\Parameter(name: 'productId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Product reviews', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Review')),
                        new OA\Property(property: 'avg_rating', type: 'number', format: 'float', example: 4.5),
                        new OA\Property(property: 'count', type: 'integer', example: 10),
                    ]
                )),
                new OA\Response(response: 404, description: 'Product not found'),
            ]
        )
    ]
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

    #[
        OA\Post(
            path: '/api/products/{productId}/reviews',
            summary: 'Submit a review for a product',
            tags: ['Reviews'],
            security: [['sanctum' => []]],
            parameters: [
                new OA\Parameter(name: 'productId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['rating'],
                    properties: [
                        new OA\Property(property: 'rating', type: 'integer', example: 5, minimum: 1, maximum: 5),
                        new OA\Property(property: 'comment', type: 'string', maxLength: 1000, example: 'Great product!'),
                        new OA\Property(property: 'order_id', type: 'integer', nullable: true, example: 1),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Review submitted', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Review submitted. It will appear after approval.'),
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'rating', type: 'integer'),
                            new OA\Property(property: 'comment', type: 'string'),
                        ], type: 'object'),
                    ]
                )),
                new OA\Response(response: 422, description: 'Duplicate review or validation error'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
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
