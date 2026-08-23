@extends('layouts.app')

@section('title', 'Orders')
@section('page-title', 'Orders')

@section('content')

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Orders
                </h1>

                <p class="text-sm text-gray-500">
                    View and manage customer orders.
                </p>
            </div>

        </div>

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-2xl shadow border p-4 flex flex-wrap items-end gap-4">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-600">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Order # or customer"
                    class="px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 w-64">
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-600">Status</label>
                <select name="status" class="px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white rounded-xl hover:bg-gray-900">Filter</button>
            <a href="{{ route('orders.index') }}" class="px-5 py-2.5 bg-gray-100 rounded-xl hover:bg-gray-200">Reset</a>
        </form>

        <div class="bg-white rounded-2xl shadow border overflow-hidden">

            @if ($orders->count())

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50 border-b">
                            <tr>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Order
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Customer
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Status
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Payment
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Method
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Total
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Date
                                </th>

                                <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">
                                    Actions
                                </th>

                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($orders as $order)
                                <tr class="border-b hover:bg-gray-50">

                                    <!-- Order -->
                                    <td class="px-6 py-4">

                                        <div>
                                            <h3 class="font-semibold text-gray-800">
                                                {{ $order->order_number }}
                                            </h3>

                                            <p class="text-sm text-gray-500">
                                                #{{ $order->id }}
                                            </p>
                                        </div>

                                    </td>

                                    <!-- Customer -->
                                    <td class="px-6 py-4">

                                        <div>
                                            <p class="font-medium text-gray-800">
                                                {{ $order->user->name }}
                                            </p>

                                            <p class="text-sm text-gray-500">
                                                {{ $order->user->email }}
                                            </p>
                                        </div>

                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        @switch($order->status)
                                            @case('pending')
                                                <span class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-700">Pending</span>
                                                @break
                                            @case('confirmed')
                                                <span class="px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-700">Confirmed</span>
                                                @break
                                            @case('processing')
                                                <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-700">Processing</span>
                                                @break
                                            @case('shipped')
                                                <span class="px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-700">Shipped</span>
                                                @break
                                            @case('delivered')
                                                <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700">Delivered</span>
                                                @break
                                            @default
                                                <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-700">Cancelled</span>
                                        @endswitch
                                    </td>

                                    <!-- Payment Status -->
                                    <td class="px-6 py-4">

                                        @if ($order->payment_status == 'paid')
                                            <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700">
                                                Paid
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-700">
                                                Pending
                                            </span>
                                        @endif

                                    </td>

                                    <!-- Method -->
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ strtoupper($order->payment_method) }}
                                    </td>

                                    <!-- Total -->
                                    <td class="px-6 py-4">

                                        <div>
                                            <p class="font-semibold text-green-600">
                                                ${{ number_format($order->total, 2) }}
                                            </p>

                                            <p class="text-xs text-gray-400">
                                                Subtotal: ${{ number_format($order->subtotal, 2) }}
                                            </p>

                                        </div>

                                    </td>

                                    <!-- Date -->
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $order->created_at->format('d M Y') }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4">

                                        <div class="flex justify-end gap-2">

                                            <a href="{{ route('orders.show', $order->id) }}"
                                                class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                                                View
                                            </a>

                                        </div>

                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="py-20 text-center">

                    <div class="text-6xl mb-4">
                        📦
                    </div>

                    <h2 class="text-xl font-bold text-gray-700">
                        No Orders Yet
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Orders from customers will appear here.
                    </p>

                </div>

            @endif

        </div>

    </div>

@endsection
