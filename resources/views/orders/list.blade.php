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

                                        @if ($order->status == 'pending')
                                            <span class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-700">
                                                Pending
                                            </span>
                                        @elseif($order->status == 'processing')
                                            <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-700">
                                                Processing
                                            </span>
                                        @elseif($order->status == 'completed')
                                            <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700">
                                                Completed
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-700">
                                                Cancelled
                                            </span>
                                        @endif

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

                                            <a href="{{ route('orders.edit', $order->id) }}"
                                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                                Edit
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
