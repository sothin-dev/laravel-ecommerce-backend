<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('products.list', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|string|max:255|unique:products,sku',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'variants' => 'nullable|array',
            'variants.*.type' => 'required_with:variants|string|max:50',
            'variants.*.value' => 'required_with:variants|string|max:100',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('products', 'public');
        }

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'],
            'stock' => $validated['stock'],
            'sku' => $validated['sku'],
            'is_active' => $validated['is_active'],
            'image' => $imagePath,
        ]);

        $this->syncVariants($product, $request);

        return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $categories = Category::all();
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'variants' => 'nullable|array',
            'variants.*.type' => 'required_with:variants|string|max:50',
            'variants.*.value' => 'required_with:variants|string|max:100',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {

            // Delete old image if it exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Store new image
            $validated['image'] = $request->file('image')
                ->store('products', 'public');
        }

        // Handle checkbox
        $validated['is_active'] = $request->boolean('is_active');

        // Update category
        $product->update($validated);

        $this->syncVariants($product, $request);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Sync product variants from the request.
     * Only runs when a "variants" input is present (so edits without variants keep existing ones).
     */
    private function syncVariants(Product $product, Request $request): void
    {
        if (! $request->has('variants')) {
            return;
        }

        $product->variants()->delete();

        foreach ((array) $request->input('variants', []) as $index => $variant) {
            if (empty($variant['type']) && empty($variant['value'])) {
                continue;
            }

            $product->variants()->create([
                'type'       => $variant['type'] ?? '',
                'value'      => $variant['value'] ?? '',
                'sku'        => $variant['sku'] ?? null,
                'price'      => $variant['price'] !== null && $variant['price'] !== '' ? $variant['price'] : null,
                'stock'      => (int) ($variant['stock'] ?? 0),
                'sort_order' => (int) $index,
            ]);
        }
    }
}
