@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Categories')

@section('content')

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Categories
                </h1>

                <p class="text-sm text-gray-500">
                    Manage your product categories.
                </p>
            </div>

            <a href="{{ route('categories.create') }}"
                class="px-5 py-3 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700">

                + Create Category

            </a>

        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow border overflow-hidden">

            @if ($categories->count())

                <!-- Table -->
                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50 border-b">

                            <tr>

                                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                                    Category
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                                    Parent
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                                    Status
                                </th>

                                <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600">
                                    Created
                                </th>

                                <th class="text-right px-6 py-4 text-sm font-semibold text-gray-600">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($categories as $category)
                                <tr class="border-b hover:bg-gray-50">

                                    <!-- Category -->
                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-4">

                                            @if ($category->image)
                                                <img src="{{ asset('storage/' . $category->image) }}"
                                                    class="w-14 h-14 rounded-xl object-cover">
                                            @else
                                                <div
                                                    class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center text-2xl">
                                                    📂
                                                </div>
                                            @endif

                                            <div>

                                                <h3 class="font-semibold text-gray-800">
                                                    {{ $category->name }}
                                                </h3>

                                                <p class="text-sm text-gray-500">
                                                    {{ $category->slug }}
                                                </p>

                                                <p class="text-xs text-gray-400 mt-1">
                                                    {{ Str::limit($category->description, 40) }}
                                                </p>

                                            </div>

                                        </div>

                                    </td>

                                    <!-- Parent -->
                                    <td class="px-6 py-4 text-gray-600">

                                        {{ optional($category->parent)->name ?? '-' }}

                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">

                                        @if ($category->is_active)
                                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">

                                                Active

                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">

                                                Inactive

                                            </span>
                                        @endif

                                    </td>

                                    <!-- Created -->
                                    <td class="px-6 py-4 text-sm text-gray-500">

                                        {{ $category->created_at->diffForHumans() }}

                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4">

                                        <div class="flex justify-end gap-2">

                                            <a href="{{ route('categories.edit', $category->id) }}"
                                                class="px-4 py-2 border rounded-lg hover:bg-gray-100">

                                                Edit

                                            </a>

                                            <button
                                                onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                                                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                                                Delete
                                            </button>
                                            @include('categories.delete', ['category' => $category])
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
                        📂
                    </div>

                    <h2 class="text-xl font-bold text-gray-700">
                        No Categories Yet
                    </h2>

                    <p class="mt-2 text-gray-500">
                        Start organizing your products by creating your first category.
                    </p>


                </div>

            @endif

        </div>

    </div>

@endsection
