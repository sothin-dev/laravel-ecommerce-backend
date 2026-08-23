<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
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
            ->with('product', 'variant')
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
                        new OA\Property(property: 'variant_id', type: 'integer', example: null, nullable: true),
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
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1|max:100',
        ]);

        $product = Product::where('is_active', true)->findOrFail($validated['product_id']);

        $variant = null;
        if (! empty($validated['variant_id'])) {
            $variant = $product->variants()->where('id', $validated['variant_id'])->first();

            if (! $variant) {
                return response()->json(['message' => 'The selected variant is invalid.'], 422);
            }
        }

        $availableStock = $variant ? $variant->stock : $product->stock;

        $item = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->first();

        $newQty = ($item ? $item->quantity : 0) + $validated['quantity'];

        if ($newQty > $availableStock) {
            return response()->json([
                'message' => "Only {$availableStock} item(s) in stock.",
            ], 422);
        }

        $item = Cart::updateOrCreate(
            [
                'user_id'    => $request->user()->id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
            ],
            ['quantity' => $newQty]
        );

        return response()->json([
            'message' => 'Cart updated.',
            'data'    => $this->formatCartItem($item->load('product', 'variant')),
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

        $availableStock = $item->variant ? $item->variant->stock : $item->product->stock;

        if ($validated['quantity'] > $availableStock) {
            return response()->json([
                'message' => "Only {$availableStock} item(s) in stock.",
            ], 422);
        }

        $item->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'message' => 'Quantity updated.',
            'data'    => $this->formatCartItem($item->load('product', 'variant')),
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
        $basePrice = $item->product->sale_price ?? $item->product->price;
        $price     = ($item->variant && $item->variant->price) ? $item->variant->price : $basePrice;
        $subtotal  = (float) $price * $item->quantity;

        return [
            'id'         => $item->id,
            'product_id' => $item->product_id,
            'variant_id' => $item->variant_id,
            'name'       => $item->product->name,
            'slug'       => $item->product->slug,
            'image_url'  => $item->product->image ? asset('storage/' . $item->product->image) : null,
            'price'      => (float) $price,
            'quantity'   => $item->quantity,
            'subtotal'   => round($subtotal, 2),
            'stock'      => $item->variant ? $item->variant->stock : $item->product->stock,
            'variant'    => $item->variant ? [
                'id'    => $item->variant->id,
                'type'  => $item->variant->type,
                'value' => $item->variant->value,
            ] : null,
        ];
    }
}
