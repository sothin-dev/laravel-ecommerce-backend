@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)
@section('page-title', 'Order #' . $order->order_number)

@section('content')

    <div class="space-y-6">

        <!-- Back link -->
        <a href="{{ route('orders.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            &larr; Back to Orders
        </a>

        <!-- Order Summary Card -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Customer Info -->
            <div class="bg-white rounded-2xl shadow border p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                    Customer
                </h3>
                <div class="space-y-2">
                    <p class="font-semibold text-gray-800">{{ $order->user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $order->user->email }}</p>
                    @if ($order->user->phone)
                        <p class="text-sm text-gray-500">{{ $order->user->phone }}</p>
                    @endif
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="bg-white rounded-2xl shadow border p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                    Shipping Address
                </h3>
                <p class="text-sm text-gray-700 leading-relaxed">
                    {{ $order->shipping_address }}
                </p>
            </div>

            <!-- Order Status -->
            <div class="bg-white rounded-2xl shadow border p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                    Order Status
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Status</span>
                        @if ($order->status == 'pending')
                            <span class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-700">
                                Pending
                            </span>
                        @elseif($order->status == 'confirmed')
                            <span class="px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-700">
                                Confirmed
                            </span>
                        @elseif($order->status == 'processing')
                            <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-700">
                                Processing
                            </span>
                        @elseif($order->status == 'shipped')
                            <span class="px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-700">
                                Shipped
                            </span>
                        @elseif($order->status == 'delivered')
                            <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700">
                                Delivered
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-700">
                                Cancelled
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Payment</span>
                        @if ($order->payment_status == 'paid')
                            <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700">
                                Paid
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-700">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Method</span>
                        <span class="text-sm font-medium text-gray-700">
                            {{ str_replace('_', ' ', ucfirst($order->payment_method)) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Date</span>
                        <span class="text-sm text-gray-700">
                            {{ $order->created_at->format('d M Y, h:i A') }}
                        </span>
                    </div>

                    <!-- Status update -->
                    <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST" class="pt-3 border-t mt-3">
                        @csrf
                        <label class="block mb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Update Status</label>
                        <div class="flex gap-2">
                            <select name="status"
                                class="flex-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-2xl shadow border overflow-hidden">

            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="font-semibold text-gray-800">
                    Order Items ({{ $order->items->count() }})
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">

                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Product</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Unit Price</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Quantity</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-600">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($item->product->image)
                                            <img src="{{ asset('storage/' . $item->product->image) }}"
                                                alt="{{ $item->product->name }}"
                                                class="w-12 h-12 rounded-lg object-cover">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $item->product->name }}</p>
                                            <p class="text-xs text-gray-400">SKU: {{ $item->product->sku }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    ${{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-800">
                                    ${{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            <!-- Totals -->
            <div class="px-6 py-4 bg-gray-50 border-t">
                <div class="ml-auto max-w-xs space-y-2">

                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span>${{ number_format($order->subtotal, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Shipping</span>
                        <span>
                            @if ($order->shipping_fee > 0)
                                ${{ number_format($order->shipping_fee, 2) }}
                            @else
                                <span class="text-green-600">Free</span>
                            @endif
                        </span>
                    </div>

                    <div class="flex justify-between text-base font-bold text-gray-800 border-t pt-2">
                        <span>Total</span>
                        <span class="text-green-600">${{ number_format($order->total, 2) }}</span>
                    </div>

                </div>
            </div>

        </div>

    </div>

@endsection
