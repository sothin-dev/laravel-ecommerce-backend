<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category:id,name,slug')->withCount('variants');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn ($q) => $q
                ->where('name', 'like', $term)
                ->orWhere('sku', 'like', $term));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        match ($request->get('sort', 'newest')) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'stock_asc'  => $query->orderBy('stock'),
            'name'       => $query->orderBy('name'),
            default      => $query->latest(),
        };

        $perPage  = min((int) $request->get('per_page', 10), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => $products->getCollection()->map(fn ($p) => $this->format($p))->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateProduct($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }
        unset($validated['variants']);

        $product = Product::create([
            ...$validated,
            'sale_price' => $validated['sale_price'] ?? null,
            'is_active'  => $request->boolean('is_active'),
        ]);

        $this->syncVariants($product, $request);

        return response()->json([
            'message' => 'Product created successfully.',
            'data'    => $this->format($product->load('variants')),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with(['category:id,name', 'variants'])->findOrFail($id);

        return response()->json([
            'data'         => $this->format($product),
            'categories'   => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product   = Product::findOrFail($id);
        $validated = $this->validateProduct($request, $product->id);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }
        unset($validated['variants'], $validated['remove_image']);

        if ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $product->image = null;
        }

        $product->update([
            ...collect($validated)->except(['image'])->all(),
            'sale_price' => $validated['sale_price'] ?? null,
            'is_active'  => $request->boolean('is_active'),
        ]);

        $this->syncVariants($product, $request);

        return response()->json([
            'message' => 'Product updated successfully.',
            'data'    => $this->format($product->load('variants')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        if ($product->orderItems()->exists()) {
            // Soft-disable instead of breaking order history
            $product->update(['is_active' => false]);
            return response()->json([
                'message' => 'Product has existing orders and was deactivated instead of deleted.',
            ]);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = $ignoreId
            ? 'required|string|max:255|unique:products,slug,' . $ignoreId
            : 'required|string|max:255|unique:products,slug';
        $skuRule = $ignoreId
            ? 'required|string|max:255|unique:products,sku,' . $ignoreId
            : 'required|string|max:255|unique:products,sku';

        return $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'name'        => 'required|string|max:255',
            'slug'        => $slugRule,
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'sale_price'  => 'nullable|numeric|min:0|lt:price',
            'stock'       => 'required|integer|min:0',
            'sku'         => $skuRule,
            'is_active'   => 'nullable|boolean',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_image' => 'nullable|boolean',
            'variants'              => 'nullable|array',
            'variants.*.type'       => 'required_with:variants|string|max:50',
            'variants.*.value'      => 'required_with:variants|string|max:100',
            'variants.*.sku'        => 'nullable|string|max:100',
            'variants.*.price'      => 'nullable|numeric|min:0',
            'variants.*.stock'      => 'required_with:variants.*|integer|min:0',
        ]);
    }

    /**
     * Replace all variants of a product when a "variants" payload is present.
     */
    private function syncVariants(Product $product, Request $request): void
    {
        if (! $request->has('variants')) {
            return;
        }

        $keepIds = [];

        foreach ((array) $request->input('variants', []) as $index => $variant) {
            if (empty($variant['type']) && empty($variant['value'])) {
                continue;
            }

            $attributes = [
                'type'       => $variant['type'] ?? '',
                'value'      => $variant['value'] ?? '',
                'sku'        => $variant['sku'] ?? null,
                'price'      => ($variant['price'] ?? null) !== null && $variant['price'] !== '' ? $variant['price'] : null,
                'stock'      => (int) ($variant['stock'] ?? 0),
                'sort_order' => (int) $index,
            ];

            if (! empty($variant['id'])) {
                $existing = $product->variants()->find($variant['id']);
                if ($existing) {
                    $existing->update($attributes);
                    $keepIds[] = $existing->id;
                    continue;
                }
            }

            $keepIds[] = $product->variants()->create($attributes)->id;
        }

        $product->variants()->whereNotIn('id', $keepIds)->delete();
    }

    private function format(Product $p): array
    {
        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'slug'        => $p->slug,
            'description' => $p->description,
            'price'       => (float) $p->price,
            'sale_price'  => $p->sale_price !== null ? (float) $p->sale_price : null,
            'stock'       => (int) $p->stock,
            'sku'         => $p->sku,
            'is_active'   => (bool) $p->is_active,
            'image_url'   => $p->image ? asset('storage/' . $p->image) : null,
            'category'    => $p->category?->only(['id', 'name', 'slug']),
            'category_id' => $p->category_id,
            'variants_count' => $p->variants_count ?? $p->variants->count(),
            'variants'    => $p->relationLoaded('variants')
                ? $p->variants->map(fn ($v) => [
                    'id'    => $v->id,
                    'type'  => $v->type,
                    'value' => $v->value,
                    'sku'   => $v->sku,
                    'price' => $v->price !== null ? (float) $v->price : null,
                    'stock' => (int) $v->stock,
                ])->values()
                : null,
            'created_at'  => $p->created_at?->toDateTimeString(),
        ];
    }
}
