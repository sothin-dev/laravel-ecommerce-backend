<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('parent')->latest()->paginate(10);
        return view('categories.category', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->with('children')->get();
        return view('categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories,slug',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean|nullable',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('categories', 'public');
        }
        Category::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'image' => $imagePath,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => $validated['is_active'] ?? 0,
        ]);

        return redirect()->route('categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::with('children')->findOrFail($id);

        // Get all categories that can be parents (excluding self and descendants)
        $excludeIds = $this->getDescendantIds($category);
        $excludeIds[] = $category->id;

        $parentCategories = Category::whereNull('parent_id')
            ->whereNotIn('id', $excludeIds)
            ->with(['children' => fn ($q) => $q->whereNotIn('id', $excludeIds)])
            ->get();

        return view('categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Recursively collect all descendant IDs of a category.
     */
    private function getDescendantIds(Category $category): array
    {
        $ids = [];
        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $child->load('children');
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'parent_id' => 'nullable|exists:categories,id|different:' . $category->id,
            'is_active' => 'nullable|boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {

            // Delete old image if it exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            // Store new image
            $validated['image'] = $request->file('image')
                ->store('categories', 'public');
        }

        // Handle checkbox
        $validated['is_active'] = $request->boolean('is_active');

        // Update category
        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
