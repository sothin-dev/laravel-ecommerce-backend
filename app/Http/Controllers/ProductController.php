<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
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
            'category_id' => 'required',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'description' => 'required|string',
            'price' => 'required',
            'sale_price' => 'required',
            'stock' => 'required',
            'sku' => 'required|string|unique:products,sku',
            'is_active' => 'boolean',
        ]);

        Product::create($validated);

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
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $product->id,
            'description' => 'required|string',
            'price' => 'required',
            'sale_price' => 'required',
            'stock' => 'required',
            'sku' => 'required|string|unique:products,sku',
            'is_active' => 'nullable|boolean',
        ]);


        // Handle checkbox
        $validated['is_active'] = $request->boolean('is_active');

        // Update category
        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        $product -> delete();

        return redirect()->route('products.index');
    }
}
