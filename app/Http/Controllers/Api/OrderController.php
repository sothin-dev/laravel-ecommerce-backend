<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[
        OA\Get(
            path: '/api/orders',
            summary: 'List the authenticated user orders',
            tags: ['Orders'],
            security: [['sanctum' => []]],
            parameters: [
                new OA\Parameter(name: 'per_page', in: 'query', description: 'Items per page (max 50)', required: false, schema: new OA\Schema(type: 'integer', default: 10)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Paginated list of orders', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Order')),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                    ]
                )),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 10), 50);

        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product', 'items.variant'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $orders->getCollection()->map(fn ($o) => $this->formatOrder($o))->values(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    #[
        OA\Get(
            path: '/api/orders/{orderNumber}',
            summary: 'Show a single order by order number',
            tags: ['Orders'],
            security: [['sanctum' => []]],
            parameters: [
                new OA\Parameter(name: 'orderNumber', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Order detail', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/OrderDetail'),
                    ]
                )),
                new OA\Response(response: 404, description: 'Order not found'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'items.variant'])
            ->firstOrFail();

        return response()->json(['data' => $this->formatOrder($order, detail: true)]);
    }

    #[
        OA\Post(
            path: '/api/checkout',
            summary: 'Checkout: convert cart to an order',
            tags: ['Orders'],
            security: [['sanctum' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['shipping_address', 'payment_method'],
                    properties: [
                        new OA\Property(property: 'shipping_address', type: 'string', maxLength: 500, example: '123 Main St, City, Country'),
                        new OA\Property(property: 'payment_method', type: 'string', enum: ['cash_on_delivery', 'bank_transfer', 'credit_card'], example: 'cash_on_delivery'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Order placed successfully', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Order placed successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/OrderDetail'),
                    ]
                )),
                new OA\Response(response: 422, description: 'Empty cart or insufficient stock'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string|max:500',
            'payment_method'   => 'required|string|in:cash_on_delivery,bank_transfer,credit_card',
            'coupon_code'      => 'nullable|string|max:50',
        ]);

        $userId    = $request->user()->id;
        $cartItems = Cart::where('user_id', $userId)->with(['product', 'variant'])->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        // Validate stock before creating order (variant-aware)
        foreach ($cartItems as $item) {
            if (! $item->product || ! $item->product->is_active) {
                return response()->json([
                    'message' => "\"{$item->name}\" is no longer available.",
                ], 422);
            }

            $availableStock = $item->variant ? $item->variant->stock : $item->product->stock;

            if ($item->quantity > $availableStock) {
                return response()->json([
                    'message' => "Insufficient stock for \"{$item->product->name}\".",
                ], 422);
            }
        }

        // Resolve coupon (if provided)
        $coupon      = null;
        $discount    = 0;
        $couponCode  = trim($validated['coupon_code'] ?? '');

        if ($couponCode !== '') {
            $coupon = Coupon::where('code', strtoupper($couponCode))->first();

            if (! $coupon) {
                return response()->json(['message' => 'Invalid coupon code.'], 422);
            }

            $evaluation = $coupon->evaluate((float) $cartItems->sum(
                fn ($item) => $this->unitPrice($item) * $item->quantity
            ), $request->user());

            if (! $evaluation['valid']) {
                return response()->json(['message' => $evaluation['message']], 422);
            }

            $discount = $evaluation['discount'];
        }

        $order = DB::transaction(function () use ($cartItems, $userId, $validated, $discount, $coupon) {
            $subtotal = $cartItems->sum(fn ($item) => $this->unitPrice($item) * $item->quantity);
            $shippingFee = $subtotal >= 50 ? 0 : 5; // free shipping over $50
            $total       = max(0, $subtotal + $shippingFee - $discount);

            $order = Order::create([
                'user_id'          => $userId,
                'order_number'     => 'ORD-' . strtoupper(Str::random(10)),
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shippingFee,
                'discount_amount'  => $discount,
                'coupon_id'        => $coupon?->id,
                'total'            => $total,
                'shipping_address' => $validated['shipping_address'],
                'payment_method'   => $validated['payment_method'],
                'payment_status'   => 'pending',
            ]);

            foreach ($cartItems as $item) {
                $unitPrice = $this->unitPrice($item);

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $unitPrice,
                    'subtotal'   => $unitPrice * $item->quantity,
                ]);

                // Decrease stock
                if ($item->variant_id) {
                    $item->variant->decrement('stock', $item->quantity);
                } else {
                    $item->product->decrement('stock', $item->quantity);
                }
            }

            // Record coupon usage
            if ($coupon) {
                $coupon->usages()->create([
                    'user_id'  => $userId,
                    'order_id' => $order->id,
                ]);
                $coupon->increment('used_count');
            }

            // Clear cart
            Cart::where('user_id', $userId)->delete();

            return $order;
        });

        return response()->json([
            'message' => 'Order placed successfully.',
            'data'    => $this->formatOrder($order->load(['items.product', 'items.variant']), detail: true),
        ], 201);
    }

    #[
        OA\Post(
            path: '/api/orders/{orderNumber}/reorder',
            summary: 'Reorder: add all items from a past order to the cart',
            tags: ['Orders'],
            security: [['sanctum' => []]],
            parameters: [
                new OA\Parameter(name: 'orderNumber', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Items added to cart', content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: '3 item(s) added to your cart.'),
                    ]
                )),
                new OA\Response(response: 404, description: 'Order not found'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function reorder(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'items.variant'])
            ->firstOrFail();

        $added   = 0;
        $skipped = 0;

        foreach ($order->items as $item) {
            $product = $item->product;
            $variant = $item->variant;

            if (!$product || !$product->is_active) {
                $skipped++;
                continue;
            }

            $availableStock = $variant ? $variant->stock : $product->stock;

            if ($availableStock < 1) {
                $skipped++;
                continue;
            }

            $cartItem = Cart::where('user_id', $request->user()->id)
                ->where('product_id', $product->id)
                ->where('variant_id', $item->variant_id)
                ->first();

            $newQty = ($cartItem ? $cartItem->quantity : 0) + $item->quantity;

            // Cap at available stock
            $newQty = min($newQty, $availableStock);

            if ($newQty < 1) {
                $skipped++;
                continue;
            }

            Cart::updateOrCreate(
                ['user_id' => $request->user()->id, 'product_id' => $product->id, 'variant_id' => $item->variant_id],
                ['quantity' => $newQty]
            );

            $added++;
        }

        $parts = [];
        if ($added > 0) {
            $parts[] = "{$added} item(s) added to your cart";
        }
        if ($skipped > 0) {
            $parts[] = "{$skipped} item(s) skipped (unavailable or out of stock)";
        }

        $message = $parts ? implode('. ', $parts) . '.' : 'No items could be added to your cart.';

        return response()->json(['message' => $message]);
    }

    #[
        OA\Post(
            path: '/api/orders/{orderNumber}/cancel',
            summary: 'Cancel an order (only when pending)',
            tags: ['Orders'],
            security: [['sanctum' => []]],
            parameters: [
                new OA\Parameter(name: 'orderNumber', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Order cancelled'),
                new OA\Response(response: 422, description: 'Order cannot be cancelled'),
                new OA\Response(response: 404, description: 'Order not found'),
                new OA\Response(response: 401, description: 'Unauthenticated'),
            ]
        )
    ]
    public function cancel(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->with(['items.product', 'items.variant'])
            ->firstOrFail();

        if (! in_array($order->status, ['pending', 'confirmed', 'processing'])) {
            return response()->json([
                'message' => 'This order can no longer be cancelled.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->variant_id && $item->variant) {
                    $item->variant->increment('stock', $item->quantity);
                } elseif ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $this->releaseCoupon($order);

            $order->update(['status' => 'cancelled']);
        });

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data'    => $this->formatOrder($order->load(['items.product', 'items.variant']), detail: true),
        ]);
    }

    /**
     * Resolve the effective unit price for a cart item (variant price overrides product price).
     */
    private function unitPrice(Cart $item): float
    {
        if ($item->variant && $item->variant->price) {
            return (float) $item->variant->price;
        }

        return (float) ($item->product->sale_price ?? $item->product->price);
    }

    /**
     * Release coupon usage when an order is cancelled.
     */
    private function releaseCoupon(Order $order): void
    {
        if (! $order->coupon_id) {
            return;
        }

        Coupon::where('id', $order->coupon_id)->decrement('used_count');
        \App\Models\CouponUsage::where('order_id', $order->id)->delete();
    }

    /**
     * Format an order for response.
     */
    private function formatOrder(Order $order, bool $detail = false): array
    {
        $base = [
            'id'               => $order->id,
            'order_number'     => $order->order_number,
            'status'           => $order->status,
            'payment_status'   => $order->payment_status,
            'payment_method'   => $order->payment_method,
            'subtotal'         => (float) $order->subtotal,
            'shipping_fee'     => (float) $order->shipping_fee,
            'discount'         => (float) $order->discount_amount,
            'coupon_code'      => $order->coupon?->code,
            'total'            => (float) $order->total,
            'shipping_address' => $order->shipping_address,
            'created_at'       => $order->created_at->toDateTimeString(),
        ];

        if ($detail) {
            $base['items'] = $order->items->map(fn ($item) => [
                'id'         => $item->id,
                'product_id' => $item->product_id,
                'name'       => $item->product->name,
                'slug'       => $item->product->slug,
                'image_url'  => $item->product->image
                    ? asset('storage/' . $item->product->image)
                    : null,
                'unit_price' => (float) $item->unit_price,
                'quantity'   => $item->quantity,
                'subtotal'   => (float) $item->subtotal,
                'variant'    => $item->variant ? [
                    'type'  => $item->variant->type,
                    'value' => $item->variant->value,
                ] : null,
            ]);
        } else {
            $base['items_count'] = $order->items->sum('quantity');
        }

        return $base;
    }
}
