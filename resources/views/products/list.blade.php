@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Products')

@section('content')

<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Products
            </h1>

            <p class="text-sm text-gray-500">
                Manage your products.
            </p>
        </div>

        <a href="{{ route('products.create') }}"
            class="px-5 py-3 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700">

            + Create Product

        </a>

    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow border overflow-hidden">

        @if ($products->count())

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-gray-50 border-b">
                        <tr>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Product
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Category
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Price
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Stock
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                SKU
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                Status
                            </th>

                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">
                                Actions
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($products as $product)

                            <tr class="border-b hover:bg-gray-50">

                                <!-- Product -->
                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-4">

                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}"
                                                class="w-14 h-14 rounded-xl object-cover">
                                        @else
                                            <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center text-2xl">
                                                📦
                                            </div>
                                        @endif

                                        <div>

                                            <h3 class="font-semibold text-gray-800">
                                                {{ $product->name }}
                                            </h3>

                                            <p class="text-sm text-gray-500">
                                                {{ $product->slug }}
                                            </p>

                                            <p class="text-xs text-gray-400 mt-1">
                                                {{ Str::limit($product->description, 40) }}
                                            </p>

                                        </div>

                                    </div>

                                </td>

                                <!-- Category -->
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $product->category->name }}
                                </td>

                                <!-- Price -->
                                <td class="px-6 py-4">

                                    <div class="space-y-1">

                                        @if ($product->sale_price)

                                            <p class="font-semibold text-green-600">
                                                ${{ number_format($product->sale_price, 2) }}
                                            </p>

                                            <p class="text-sm text-gray-400 line-through">
                                                ${{ number_format($product->price, 2) }}
                                            </p>

                                        @else

                                            <p class="font-semibold">
                                                ${{ number_format($product->price, 2) }}
                                            </p>

                                        @endif

                                    </div>

                                </td>

                                <!-- Stock -->
                                <td class="px-6 py-4">

                                    @if ($product->stock > 10)
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                                            {{ $product->stock }} In Stock
                                        </span>
                                    @elseif ($product->stock > 0)
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">
                                            {{ $product->stock }} Low Stock
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">
                                            Out of Stock
                                        </span>
                                    @endif

                                </td>

                                <!-- SKU -->
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $product->sku }}
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">

                                    @if ($product->is_active)

                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                                            Active
                                        </span>

                                    @else

                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">
                                            Inactive
                                        </span>

                                    @endif

                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4">

                                    <div class="flex justify-end gap-2">

                                        <a href="{{ route('products.edit', $product->id) }}"
                                            class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                                            Edit
                                        </a>

                                        <button
                                            onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                                            Delete
                                        </button>

                                        @include('products.delete', ['product' => $product])

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <!-- Empty State -->
            <div class="py-20 text-center">

                <div class="text-6xl mb-4">
                    📦
                </div>

                <h2 class="text-xl font-bold text-gray-700">
                    No Products Yet
                </h2>

                <p class="mt-2 text-gray-500">
                    Start selling by creating your first product.
                </p>

            </div>

        @endif

    </div>

</div>

@endsection