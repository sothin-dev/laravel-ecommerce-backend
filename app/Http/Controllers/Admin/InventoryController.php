<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category:id,name', 'variants'])
            ->withSum('orderItems as total_sold', 'quantity');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn ($q) => $q
                ->where('name', 'like', $term)
                ->orWhere('sku', 'like', $term));
        }

        if ($request->filled('filter')) {
            match ($request->filter) {
                'low_stock'    => $query->where('stock', '<=', 5)->where('stock', '>', 0),
                'out_of_stock' => $query->where('stock', 0),
                default        => null,
            };
        }

        $perPage  = min((int) $request->get('per_page', 15), 50);
        $products = $query->orderBy('stock')->paginate($perPage);

        $stats = [
            'total_products'   => Product::count(),
            'out_of_stock'     => Product::where('stock', 0)->count(),
            'low_stock'        => Product::where('stock', '<=', 5)->where('stock', '>', 0)->count(),
            'total_variants'   => ProductVariant::count(),
            'variant_out_of_stock' => ProductVariant::where('stock', 0)->count(),
        ];

        return response()->json([
            'data'  => $products->getCollection()->map(fn ($p) => $this->format($p))->values(),
            'meta'  => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Adjust stock for a product or one of its variants.
     */
    public function adjustStock(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'stock'      => 'required|integer|min:0|max:1000000',
        ]);

        $product = Product::findOrFail($id);

        if (! empty($validated['variant_id'])) {
            $variant = $product->variants()->findOrFail($validated['variant_id']);
            $variant->update(['stock' => $validated['stock']]);

            return response()->json([
                'message' => "Stock updated for {$product->name} ({$variant->type}: {$variant->value}).",
                'data'    => $this->format($product->fresh(['category:id,name', 'variants'])),
            ]);
        }

        $product->update(['stock' => $validated['stock']]);

        return response()->json([
            'message' => "Stock updated for {$product->name}.",
            'data'    => $this->format($product->fresh(['category:id,name', 'variants'])),
        ]);
    }

    private function format(Product $p): array
    {
        return [
            'id'         => $p->id,
            'name'       => $p->name,
            'sku'        => $p->sku,
            'image_url'  => $p->image ? asset('storage/' . $p->image) : null,
            'is_active'  => (bool) $p->is_active,
            'stock'      => (int) $p->stock,
            'total_sold' => (int) ($p->total_sold ?? 0),
            'category'   => $p->category?->only(['id', 'name']),
            'variants'   => $p->variants->map(fn ($v) => [
                'id'    => $v->id,
                'type'  => $v->type,
                'value' => $v->value,
                'sku'   => $v->sku,
                'stock' => (int) $v->stock,
            ])->values(),
        ];
    }
}
