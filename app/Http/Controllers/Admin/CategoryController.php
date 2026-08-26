<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::with('parent:id,name')->withCount('products');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('slug', 'like', $term));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->get('all')) {
            return response()->json([
                'data' => Category::orderBy('name')->get()->map(fn ($c) => $this->format($c)),
            ]);
        }

        $perPage = min((int) $request->get('per_page', 10), 50);
        $categories = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $categories->getCollection()->map(fn ($c) => $this->format($c))->values(),
            'meta' => $this->meta($categories),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:categories,slug',
            'description' => 'required|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'parent_id'   => 'nullable|integer|exists:categories,id',
            'is_active'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create([
            ...collect($validated)->except(['image'])->all(),
            'image'     => $validated['image'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'data'    => $this->format($category),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::with(['children'])->findOrFail($id);

        // Categories that may be assigned as parent (excluding self + descendants)
        $excludeIds = $this->descendantIds($category);
        $excludeIds[] = $category->id;

        return response()->json([
            'data'         => $this->format($category),
            'parent_options' => Category::whereNull('parent_id')
                ->whereNotIn('id', $excludeIds)
                ->get(['id', 'name'])
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'required|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'parent_id'   => [
                'nullable', 'integer', 'exists:categories,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($category) {
                    if ((int) $value === $category->id) {
                        $fail('A category cannot be its own parent.');
                    }
                    if (in_array((int) $value, $this->descendantIds($category), true)) {
                        $fail('A category cannot be moved under one of its own sub-categories.');
                    }
                },
            ],
            'is_active'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update([
            ...collect($validated)->except(['image'])->all(),
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json([
            'message' => 'Category updated successfully.',
            'data'    => $this->format($category),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a category that has sub-categories.',
            ], 422);
        }

        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a category that has products.',
            ], 422);
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    private function descendantIds(Category $category, array &$visited = []): array
    {
        $ids = [];
        foreach ($category->children as $child) {
            if (in_array($child->id, $visited, true)) {
                continue;
            }
            $visited[] = $child->id;
            $ids[] = $child->id;
            $child->load('children');
            $ids = array_merge($ids, $this->descendantIds($child, $visited));
        }
        return $ids;
    }

    private function format(Category $c): array
    {
        return [
            'id'          => $c->id,
            'name'        => $c->name,
            'slug'        => $c->slug,
            'description' => $c->description,
            'image_url'   => $c->image ? asset('storage/' . $c->image) : null,
            'parent_id'   => $c->parent_id,
            'parent_name' => $c->parent?->name,
            'is_active'   => (bool) $c->is_active,
            'products_count' => $c->products_count ?? $c->products()->count(),
            'created_at'  => $c->created_at?->toDateTimeString(),
        ];
    }

    private function meta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ];
    }
}
