@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Products')

@section('content')

<div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto">

        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-6 border-b">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Create Product
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Fill in the information below to update a new product.
                </p>
            </div>

            <a href="{{ route('products.index') }}"
                class="text-3xl text-gray-400 hover:text-red-500">
                &times;
            </a>
        </div>

        <form action="{{ route('products.update', $product->id) }}"
            method="POST"
            enctype="multipart/form-data"
            class="p-8 space-y-6">

            @csrf
            @method('PATCH')

            <div class="grid md:grid-cols-2 gap-6">

                <!-- Category -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700">
                        Category
                    </label>

                    <select name="category_id"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500">

                        <option value="">Select Category</option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach

                    </select>

                    @error('category_id')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Product Name -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700">
                        Product Name
                    </label>

                    <input type="text"
                        name="name"
                        value="{{ old('name', $product->name) }}"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500"
                        placeholder="iPhone 16">

                    @error('name')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700">
                        Slug
                    </label>

                    <input type="text"
                        name="slug"
                        value="{{ old('slug', $product->slug) }}"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500"
                        placeholder="iphone-16">

                    @error('slug')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- SKU -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700">
                        SKU
                    </label>

                    <input type="text"
                        name="sku"
                        value="{{ old('sku', $product->sku) }}"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500"
                        placeholder="IPH16-BLK-128">

                    @error('sku')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Price -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700">
                        Price ($)
                    </label>

                    <input type="number"
                        step="0.01"
                        name="price"
                        value="{{ old('price', $product->price) }}"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500"
                        placeholder="1000">

                    @error('price')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Sale Price -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700">
                        Sale Price ($)
                    </label>

                    <input type="number"
                        step="0.01"
                        name="sale_price"
                        value="{{ old('sale_price', $product->sale_price) }}"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500"
                        placeholder="950">

                    @error('sale_price')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Stock -->
                <div>
                    <label class="block mb-2 font-medium text-gray-700">
                        Stock Quantity
                    </label>

                    <input type="number"
                        name="stock"
                        value="{{ old('stock', $product->stock) }}"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500"
                        placeholder="50">

                    @error('stock')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

            <!-- Description -->
            <div>
                <label class="block mb-2 font-medium text-gray-700">
                    Description
                </label>

                <textarea rows="5"
                    name="description"
                    class="w-full px-4 py-3 border rounded-xl resize-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Write product description...">{{ old('description', $product->description) }}</textarea>

                @error('description')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Image -->
            {{-- <div>
                <label class="block mb-2 font-medium text-gray-700">
                    Product Image
                </label>

                <input type="file"
                    name="image"
                    class="w-full px-4 py-3 border rounded-xl"
                    accept="image/*">
            </div> --}}

            <!-- Status -->
            <div class="flex items-center gap-3">
                <input type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                    class="w-5 h-5">

                <span class="text-gray-700">
                    Active Product
                </span>
            </div>

            <!-- Footer -->
            <div class="border-t pt-5 flex justify-end gap-3">

                <a href="{{ route('products.index') }}"
                    class="px-6 py-3 bg-gray-200 rounded-xl hover:bg-gray-300">
                    Cancel
                </a>

                <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    Save Product
                </button>

            </div>

        </form>

    </div>

</div>

@endsection