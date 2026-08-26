<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount('orders');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn ($q) => $q
                ->where('name', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhere('phone', 'like', $term));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $perPage = min((int) $request->get('per_page', 10), 50);
        $users   = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $users->getCollection()->map(fn ($u) => $this->format($u))->values(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::withCount('orders')->findOrFail($id);

        $stats = [
            'orders_count' => $user->orders_count,
            'total_spent'  => (float) $user->orders()->whereNotIn('status', ['cancelled'])->sum('total'),
            'last_order_at' => $user->orders()->latest()?->first()?->created_at?->toDateTimeString(),
        ];

        return response()->json([
            'data' => [...$this->format($user), 'stats' => $stats],
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);

        return response()->json([
            'message' => $user->is_active
                ? "Customer {$user->name} has been activated."
                : "Customer {$user->name} has been deactivated.",
            'data' => $this->format($user),
        ]);
    }

    private function format(User $u): array
    {
        return [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'phone'      => $u->phone,
            'avatar_url' => $u->avatar ? asset('storage/' . $u->avatar) : null,
            'is_active'  => (bool) $u->is_active,
            'joined_at'  => $u->created_at?->toDateTimeString(),
        ];
    }
}
