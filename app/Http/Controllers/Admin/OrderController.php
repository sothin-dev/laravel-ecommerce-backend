<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('user:id,name,email');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', $term)
                  ->orWhereHas('user', fn ($u) => $u
                      ->where('name', 'like', $term)
                      ->orWhere('email', 'like', $term));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $perPage = min((int) $request->get('per_page', 10), 50);
        $orders  = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $orders->getCollection()->map(fn ($o) => $this->format($o))->values(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::with(['user', 'items.product:id,name,slug,image', 'items.variant', 'coupon:id,code'])
            ->findOrFail($id);

        return response()->json(['data' => $this->format($order, detail: true)]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::with(['items.product', 'items.variant'])->findOrFail($id);

        if ($order->status === 'cancelled' && $validated['status'] !== 'cancelled') {
            return response()->json([
                'message' => 'A cancelled order cannot be reopened. Please create a new order.',
            ], 422);
        }

        DB::transaction(function () use ($order, $validated) {
            if ($validated['status'] === 'cancelled' && $order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->variant_id && $item->variant) {
                        $item->variant->increment('stock', $item->quantity);
                    } elseif ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }

                if ($order->coupon_id) {
                    Coupon::where('id', $order->coupon_id)->decrement('used_count');
                    CouponUsage::where('order_id', $order->id)->delete();
                }
            }

            if (in_array($validated['status'], ['shipped', 'delivered']) && $order->payment_method !== 'cash_on_delivery') {
                $order->payment_status = 'paid';
            }

            $order->status = $validated['status'];
            $order->save();
        });

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data'    => $this->format($order->fresh(['user', 'items.product', 'items.variant']), detail: true),
        ]);
    }

    private function format(Order $o, bool $detail = false): array
    {
        $base = [
            'id'               => $o->id,
            'order_number'     => $o->order_number,
            'status'           => $o->status,
            'payment_status'   => $o->payment_status,
            'payment_method'   => $o->payment_method,
            'subtotal'         => (float) $o->subtotal,
            'shipping_fee'     => (float) $o->shipping_fee,
            'discount'         => (float) $o->discount_amount,
            'coupon_code'      => $o->coupon?->code ?? ($detail ? $o->coupon?->code : null),
            'total'            => (float) $o->total,
            'shipping_address' => $o->shipping_address,
            'created_at'       => $o->created_at->toDateTimeString(),
        ];

        if (! $detail) {
            $base['customer'] = $o->user?->only(['id', 'name', 'email']);
            $base['items_count'] = $o->items()->sum('quantity');
            return $base;
        }

        $base['customer'] = $o->user?->only(['id', 'name', 'email', 'phone']);
        $base['coupon_code'] = $o->coupon?->code;
        $base['items'] = $o->items->map(fn ($item) => [
            'id'         => $item->id,
            'product_id' => $item->product_id,
            'name'       => $item->product?->name ?? 'Deleted product',
            'slug'       => $item->product?->slug,
            'image_url'  => $item->product?->image ? asset('storage/' . $item->product->image) : null,
            'unit_price' => (float) $item->unit_price,
            'quantity'   => $item->quantity,
            'subtotal'   => (float) $item->subtotal,
            'variant'    => $item->variant ? [
                'id'    => $item->variant->id,
                'type'  => $item->variant->type,
                'value' => $item->variant->value,
            ] : null,
        ]);

        return $base;
    }
}
