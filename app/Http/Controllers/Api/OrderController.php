<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * List the authenticated user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items.product')
            ->latest()
            ->get()
            ->map(fn ($o) => $this->formatOrder($o));

        return response()->json(['data' => $orders]);
    }

    /**
     * Show a single order by order number.
     */
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->with('items.product')
            ->firstOrFail();

        return response()->json(['data' => $this->formatOrder($order, detail: true)]);
    }

    /**
     * Checkout: convert cart to an order.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string|max:500',
            'payment_method'   => 'required|string|in:cash_on_delivery,bank_transfer,credit_card',
        ]);

        $userId    = $request->user()->id;
        $cartItems = Cart::where('user_id', $userId)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        // Validate stock before creating order
        foreach ($cartItems as $item) {
            if (! $item->product->is_active || $item->quantity > $item->product->stock) {
                return response()->json([
                    'message' => "Insufficient stock for \"{$item->product->name}\".",
                ], 422);
            }
        }

        $order = DB::transaction(function () use ($cartItems, $userId, $validated) {

            $subtotal = $cartItems->sum(fn ($item) =>
                ($item->product->sale_price ?? $item->product->price) * $item->quantity
            );
            $shippingFee = $subtotal >= 50 ? 0 : 5; // free shipping over $50
            $total       = $subtotal + $shippingFee;

            $order = Order::create([
                'user_id'          => $userId,
                'order_number'     => 'ORD-' . strtoupper(Str::random(10)),
                'status'           => 'pending',
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shippingFee,
                'total'            => $total,
                'shipping_address' => $validated['shipping_address'],
                'payment_method'   => $validated['payment_method'],
                'payment_status'   => 'pending',
            ]);

            foreach ($cartItems as $item) {
                $unitPrice = $item->product->sale_price ?? $item->product->price;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $unitPrice,
                    'subtotal'   => $unitPrice * $item->quantity,
                ]);

                // Decrease stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear cart
            Cart::where('user_id', $userId)->delete();

            return $order;
        });

        return response()->json([
            'message' => 'Order placed successfully.',
            'data'    => $this->formatOrder($order->load('items.product'), detail: true),
        ], 201);
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
            ]);
        } else {
            $base['items_count'] = $order->items->sum('quantity');
        }

        return $base;
    }
}
