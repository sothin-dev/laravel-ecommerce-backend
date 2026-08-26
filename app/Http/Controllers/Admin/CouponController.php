<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Coupon::query();

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active'   => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                'expired'  => $query->whereNotNull('expires_at')->where('expires_at', '<', now()),
                default    => null,
            };
        }

        $perPage = min((int) $request->get('per_page', 10), 50);
        $coupons = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $coupons->getCollection()->map(fn ($c) => $this->format($c))->values(),
            'meta' => [
                'current_page' => $coupons->currentPage(),
                'last_page'    => $coupons->lastPage(),
                'per_page'     => $coupons->perPage(),
                'total'        => $coupons->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateCoupon($request);

        $validated['code']      = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);

        $coupon = Coupon::create($validated);

        return response()->json([
            'message' => 'Coupon created successfully.',
            'data'    => $this->format($coupon),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => $this->format(Coupon::findOrFail($id))]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $coupon    = Coupon::findOrFail($id);
        $validated = $this->validateCoupon($request, $coupon->id);

        $validated['code']      = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);

        $coupon->update($validated);

        return response()->json([
            'message' => 'Coupon updated successfully.',
            'data'    => $this->format($coupon),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        Coupon::findOrFail($id)->delete();

        return response()->json(['message' => 'Coupon deleted successfully.']);
    }

    private function validateCoupon(Request $request, ?int $ignoreId = null): array
    {
        $codeRule = $ignoreId
            ? 'required|string|max:50|unique:coupons,code,' . $ignoreId
            : 'required|string|max:50|unique:coupons,code';

        return $request->validate([
            'code'             => $codeRule,
            'type'             => 'required|in:percentage,fixed',
            'value'            => 'required|numeric|min:0|max:100',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'starts_at'        => 'nullable|date',
            'expires_at'       => 'nullable|date|after_or_equal:starts_at',
            'is_active'        => 'nullable|boolean',
            'description'      => 'nullable|string|max:500',
        ]);
    }

    private function format(Coupon $c): array
    {
        return [
            'id'               => $c->id,
            'code'             => $c->code,
            'type'             => $c->type,
            'value'            => (float) $c->value,
            'min_order_amount' => $c->min_order_amount !== null ? (float) $c->min_order_amount : null,
            'max_discount'     => $c->max_discount !== null ? (float) $c->max_discount : null,
            'usage_limit'      => $c->usage_limit,
            'used_count'       => (int) $c->used_count,
            'starts_at'        => $c->starts_at?->toDateString(),
            'expires_at'       => $c->expires_at?->toDateString(),
            'is_expired'       => $c->expires_at && $c->expires_at->lessThan(now()),
            'is_active'        => (bool) $c->is_active,
            'is_currently_active' => $c->isCurrentlyActive(),
            'description'      => $c->description,
            'created_at'       => $c->created_at?->toDateTimeString(),
        ];
    }
}
