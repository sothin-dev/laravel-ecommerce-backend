<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    #[
        OA\Get(
            path: '/api/products',
            summary: 'List products with filtering, search, and pagination',
            tags: ['Products'],
            parameters: [
                new OA\Parameter(name: 'category', in: 'query', description: 'Filter by category slug', required: false, schema: new OA\Schema(type: 'string')),
                new OA\Parameter(name: 'search', in: 'query', description: 'Search by keyword (name, description, SKU)', required: false, schema: new OA\Schema(type: 'string')),
                new OA\Parameter(name: 'min_price', in: 'query', description: 'Minimum price filter', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
                new OA\Parameter(name: 'max_price', in: 'query', description: 'Maximum price filter', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
                new OA\Parameter(name: 'sort', in: 'query', description: 'Sort order', required: false, schema: new OA\Schema(type: 'string', enum: ['price_asc', 'price_desc', 'newest', 'name', 'on_sale'])),
                new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page (max 50)', required: false, schema: new OA\Schema(type: 'integer', default: 16)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Paginated list of products', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ]
                )),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'images'])
            ->where('is_active', true);

        // Category filter — include child categories
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)
                ->where('is_active', true)
                ->with('children')
                ->first();

            if ($category) {
                $categoryIds = collect([$category->id]);

                foreach ($category->children as $child) {
                    $categoryIds->push($child->id);
                }

                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->whereNull('id'); // no results
            }
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

    #[
        OA\Get(
            path: '/api/products/{slug}',
            summary: 'Show product detail by slug',
            tags: ['Products'],
            parameters: [
                new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Product detail', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ProductDetail'),
                    ]
                )),
                new OA\Response(response: 404, description: 'Product not found'),
            ]
        )
    ]
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
