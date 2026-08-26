<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user:id,name', 'product:id,name,slug']);

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('comment', 'like', $term)
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term))
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $term));
            });
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'approved' => $query->where('is_approved', true),
                'pending'  => $query->where('is_approved', false),
                default    => null,
            };
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        $perPage = min((int) $request->get('per_page', 12), 50);
        $reviews = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $reviews->getCollection()->map(fn ($r) => $this->format($r))->values(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'per_page'     => $reviews->perPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }

    public function approve(int $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => true]);

        return response()->json([
            'message' => 'Review approved.',
            'data'    => $this->format($review),
        ]);
    }

    public function hide(int $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => false]);

        return response()->json([
            'message' => 'Review hidden.',
            'data'    => $this->format($review),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        Review::findOrFail($id)->delete();

        return response()->json(['message' => 'Review deleted successfully.']);
    }

    private function format(Review $r): array
    {
        return [
            'id'          => $r->id,
            'rating'      => $r->rating,
            'comment'     => $r->comment,
            'is_approved' => (bool) $r->is_approved,
            'user_name'   => $r->user?->name,
            'product'     => $r->product?->only(['id', 'name', 'slug']),
            'created_at'  => $r->created_at?->toDateTimeString(),
        ];
    }
}
