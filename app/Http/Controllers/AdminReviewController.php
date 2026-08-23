<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Review::with(['user', 'product']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('comment', 'like', "%{$term}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"))
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        $reviews = $query->latest()->paginate(12)->withQueryString();

        return view('reviews.index', compact('reviews'));
    }

    public function approve(int $id): RedirectResponse
    {
        Review::findOrFail($id)->update(['is_approved' => true]);

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review approved.');
    }

    public function hide(int $id): RedirectResponse
    {
        Review::findOrFail($id)->update(['is_approved' => false]);

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review hidden.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Review::findOrFail($id)->delete();

        return redirect()
            ->route('reviews.index')
            ->with('success', 'Review deleted successfully.');
    }
}
