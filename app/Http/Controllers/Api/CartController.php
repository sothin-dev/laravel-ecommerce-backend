<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get authenticated user's cart.
     */
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

    /**
     * Add a product to cart or increase quantity.
     */
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

    /**
     * Update cart item quantity.
     */
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

    /**
     * Remove a single cart item.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = Cart::where('user_id', $request->user()->id)->findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item removed from cart.']);
    }

    /**
     * Clear entire cart.
     */
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
