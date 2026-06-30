<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CartController extends Controller
{
    #[
        OA\Get(
            path: '/api/cart',
            summary: 'Get authenticated user\'s cart',
            tags: ['Cart'],
            security: [['sanctum' => []]],
            responses: [
                new OA\Response(response: 200, description: 'Cart contents', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CartItem')),
                        new OA\Property(property: 'subtotal', type: 'number', format: 'float', example: 159.98),
                        new OA\Property(property: 'count', type: 'integer', example: 3),
                    ]
                )),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $items = Cart::where('user_id', $request->user()->id)
            ->with('product')
            ->get();

        $formatted = $items->map(fn ($c) => $this->formatCartItem($c));
        $subtotal  = $formatted->sum(fn ($c) => $c['subtotal']);

        return response()->json([
            'data'     => $formatted,
            'subtotal' => round($subtotal, 2),
            'count'    => $items->sum('quantity'),
        ]);
    }

    #[
        OA\Post(
            path: '/api/cart',
            summary: 'Add a product to cart or increase quantity',
            tags: ['Cart'],
            security: [['sanctum' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['product_id', 'quantity'],
                    properties: [
                        new OA\Property(property: 'product_id', type: 'integer', example: 1),
                        new OA\Property(property: 'quantity', type: 'integer', example: 2, minimum: 1, maximum: 100),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Cart updated', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Cart updated.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/CartItem'),
                    ]
                )),
                new OA\Response(response: 422, description: 'Validation error or insufficient stock'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:100',
        ]);

        $product = Product::where('is_active', true)->findOrFail($validated['product_id']);

        $item = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        $newQty = ($item ? $item->quantity : 0) + $validated['quantity'];

        if ($newQty > $product->stock) {
            return response()->json([
                'message' => "Only {$product->stock} items in stock.",
            ], 422);
        }

        $item = Cart::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            ['quantity' => $newQty]
        );

        return response()->json([
            'message' => 'Cart updated.',
            'data'    => $this->formatCartItem($item->load('product')),
        ], 201);
    }

    #[
        OA\Patch(
            path: '/api/cart/{id}',
            summary: 'Update cart item quantity',
            tags: ['Cart'],
            security: [['sanctum' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['quantity'],
                    properties: [
                        new OA\Property(property: 'quantity', type: 'integer', example: 3, minimum: 1, maximum: 100),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Quantity updated', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Quantity updated.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/CartItem'),
                    ]
                )),
                new OA\Response(response: 404, description: 'Cart item not found'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $item = Cart::where('user_id', $request->user()->id)->findOrFail($id);

        if ($validated['quantity'] > $item->product->stock) {
            return response()->json([
                'message' => "Only {$item->product->stock} items in stock.",
            ], 422);
        }

        $item->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'message' => 'Quantity updated.',
            'data'    => $this->formatCartItem($item->load('product')),
        ]);
    }

    #[
        OA\Delete(
            path: '/api/cart/{id}',
            summary: 'Remove a single cart item',
            tags: ['Cart'],
            security: [['sanctum' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Item removed', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
                new OA\Response(response: 404, description: 'Cart item not found'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = Cart::where('user_id', $request->user()->id)->findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item removed from cart.']);
    }

    #[
        OA\Delete(
            path: '/api/cart',
            summary: 'Clear entire cart',
            tags: ['Cart'],
            security: [['sanctum' => []]],
            responses: [
                new OA\Response(response: 200, description: 'Cart cleared', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function clear(Request $request): JsonResponse
    {
        Cart::where('user_id', $request->user()->id)->delete();

        return response()->json(['message' => 'Cart cleared.']);
    }

    /**
     * Format a cart item for response.
     */
    private function formatCartItem(Cart $item): array
    {
        $price    = $item->product->sale_price ?? $item->product->price;
        $subtotal = (float) $price * $item->quantity;

        return [
            'id'         => $item->id,
            'product_id' => $item->product_id,
            'name'       => $item->product->name,
            'slug'       => $item->product->slug,
            'image_url'  => $item->product->image
                ? asset('storage/' . $item->product->image)
                : null,
            'price'      => (float) $price,
            'quantity'   => $item->quantity,
            'subtotal'   => round($subtotal, 2),
            'stock'      => $item->product->stock,
        ];
    }
}
