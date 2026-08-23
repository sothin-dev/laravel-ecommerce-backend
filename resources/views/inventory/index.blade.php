@extends('layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Inventory')

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">Inventory</h1>
        <p class="text-sm text-gray-500">Track stock levels and sold quantities for every product.</p>
    </div>

    <div class="bg-white rounded-2xl shadow border overflow-hidden">

        @if ($products->count())

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Product</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">SKU</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Category</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Current Stock</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Sold</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" class="w-10 h-10 rounded-lg object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-lg">📦</div>
                                        @endif
                                        <span class="font-medium text-gray-800">{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $product->sku }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $product->category->name ?? '-' }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-800">{{ $product->stock }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $product->order_items_sum_quantity ?? 0 }}</td>
                                <td class="px-6 py-4">
                                    @if ($product->stock === 0)
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">Out of Stock</span>
                                    @elseif ($product->stock <= 10)
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">Low Stock</span>
                                    @else
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Normal</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50">
                {{ $products->links() }}
            </div>
        @else
            <div class="py-20 text-center">
                <div class="text-6xl mb-4">📦</div>
                <h2 class="text-xl font-bold text-gray-700">No Products</h2>
            </div>
        @endif

    </div>

</div>

@endsection
