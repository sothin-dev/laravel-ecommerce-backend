<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    #[
        OA\Get(
            path: '/api/categories',
            summary: 'List all active categories',
            tags: ['Categories'],
            responses: [
                new OA\Response(response: 200, description: 'List of categories', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Category')),
                    ]
                )),
            ]
        )
    ]
    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->with('parent')
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get()
            ->map(fn ($cat) => [
                'id'             => $cat->id,
                'name'           => $cat->name,
                'slug'           => $cat->slug,
                'description'    => $cat->description,
                'image_url'      => $cat->image ? asset('storage/' . $cat->image) : null,
                'products_count' => $cat->products_count,
                'parent_id'      => $cat->parent_id,
                'parent_name'    => $cat->parent?->name,
            ]);

        return response()->json(['data' => $categories]);
    }

    #[
        OA\Get(
            path: '/api/categories/{slug}',
            summary: 'Show a single category',
            tags: ['Categories'],
            parameters: [
                new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Category detail', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Category'),
                    ]
                )),
                new OA\Response(response: 404, description: 'Category not found'),
            ]
        )
    ]
    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id'          => $category->id,
                'name'        => $category->name,
                'slug'        => $category->slug,
                'description' => $category->description,
                'image_url'   => $category->image ? asset('storage/' . $category->image) : null,
            ],
        ]);
    }
}
