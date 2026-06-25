<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get authenticated user's wishlist.
     */
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

    /**
     * Toggle a product in the wishlist (add if absent, remove if present).
     */
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
