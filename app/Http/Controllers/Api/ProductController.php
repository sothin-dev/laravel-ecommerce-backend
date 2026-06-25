<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List products with filtering, search, and pagination.
     *
     * Query params:
     *   ?category=slug
     *   ?search=keyword
     *   ?min_price=0&max_price=1000
     *   ?sort=price_asc|price_desc|newest|name|on_sale
     *   ?per_page=16
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'images'])
            ->where('is_active', true);

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) =>
                $q->where('slug', $request->category)->where('is_active', true)
            );
        }

        // Search
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn ($q) =>
                $q->where('name', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhere('sku', 'like', $term)
            );
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // On sale filter
        if ($request->get('sort') === 'on_sale') {
            $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
        }

        // Sorting
        match ($request->get('sort', 'newest')) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name'       => $query->orderBy('name', 'asc'),
            'on_sale'    => $query->orderBy('created_at', 'desc'),
            default      => $query->latest(),
        };

        $perPage  = min((int) $request->get('per_page', 16), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'data'  => $products->getCollection()->map(fn ($p) => $this->formatProduct($p))->values(),
            'meta'  => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    /**
     * Show product detail by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['category', 'images', 'reviews.user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $approvedReviews = $product->reviews->where('is_approved', true);

        return response()->json([
            'data' => [
                ...$this->formatProduct($product),
                'description'  => $product->description,
                'sku'          => $product->sku,
                'stock'        => $product->stock,
                'images'       => $product->images->map(fn ($img) => [
                    'id'  => $img->id,
                    'url' => asset('storage/' . $img->image_path),
                    'alt' => $img->alt_text ?? $product->name,
                ]),
                'reviews'      => $approvedReviews->map(fn ($r) => [
                    'id'         => $r->id,
                    'rating'     => $r->rating,
                    'comment'    => $r->comment,
                    'user_name'  => $r->user->name,
                    'created_at' => $r->created_at->toDateString(),
                ])->values(),
                'avg_rating'   => $approvedReviews->count() ? round($approvedReviews->avg('rating'), 1) : null,
                'review_count' => $approvedReviews->count(),
            ],
        ]);
    }

    /**
     * Format a product for API response.
     */
    private function formatProduct(Product $product): array
    {
        $displayPrice = $product->sale_price ?? $product->price;
        $imageUrl     = $product->image
            ? asset('storage/' . $product->image)
            : ($product->images->first()
                ? asset('storage/' . $product->images->first()->image_path)
                : null);

        return [
            'id'            => $product->id,
            'name'          => $product->name,
            'slug'          => $product->slug,
            'price'         => (float) $product->price,
            'sale_price'    => $product->sale_price ? (float) $product->sale_price : null,
            'display_price' => (float) $displayPrice,
            'on_sale'       => $product->sale_price !== null && $product->sale_price < $product->price,
            'image_url'     => $imageUrl,
            'stock'         => $product->stock,
            'in_stock'      => $product->stock > 0,
            'category'      => [
                'id'   => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ],
        ];
    }
}
