<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class WishlistController extends Controller
{
    #[
        OA\Get(
            path: '/api/wishlist',
            summary: 'Get authenticated user\'s wishlist',
            tags: ['Wishlist'],
            security: [['sanctum' => []]],
            responses: [
                new OA\Response(response: 200, description: 'Wishlist items', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/WishlistItem')),
                    ]
                )),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $items = Wishlist::where('user_id', $request->user()->id)
            ->with(['product.category'])
            ->get()
            ->map(fn ($w) => [
                'id'         => $w->id,
                'product_id' => $w->product_id,
                'name'       => $w->product->name,
                'slug'       => $w->product->slug,
                'price'      => (float) $w->product->price,
                'sale_price' => $w->product->sale_price ? (float) $w->product->sale_price : null,
                'image_url'  => $w->product->image ? asset('storage/' . $w->product->image) : null,
                'in_stock'   => $w->product->stock > 0,
                'added_at'   => $w->created_at->toDateTimeString(),
            ]);

        return response()->json(['data' => $items]);
    }

    #[
        OA\Post(
            path: '/api/wishlist/{productId}',
            summary: 'Toggle a product in the wishlist (add if absent, remove if present)',
            tags: ['Wishlist'],
            security: [['sanctum' => []]],
            parameters: [
                new OA\Parameter(name: 'productId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Product removed from wishlist', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Product removed from wishlist.'),
                        new OA\Property(property: 'wishlisted', type: 'boolean', example: false),
                    ]
                )),
                new OA\Response(response: 201, description: 'Product added to wishlist', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Product added to wishlist.'),
                        new OA\Property(property: 'wishlisted', type: 'boolean', example: true),
                    ]
                )),
                new OA\Response(response: 404, description: 'Product not found'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function toggle(Request $request, int $productId): JsonResponse
    {
        $product = Product::where('is_active', true)->findOrFail($productId);
        $userId  = $request->user()->id;

        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'message'    => 'Product removed from wishlist.',
                'wishlisted' => false,
            ]);
        }

        Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'message'    => 'Product added to wishlist.',
            'wishlisted' => true,
        ], 201);
    }
}
