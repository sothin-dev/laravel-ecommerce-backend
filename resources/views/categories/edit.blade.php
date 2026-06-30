@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Categories')

@section('content')

    <div class="space-y-6">

        <div class=" fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">

            <div class="bg-white w-full max-w-3xl rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto">

                <!-- Header -->
                <div class="flex items-center justify-between px-8 py-6 border-b">

                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">
                            Update Category
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Edit the category details below.
                        </p>
                    </div>

                    <a href="{{ route('categories.index') }}" type="button" class="text-gray-400 hover:text-red-500 text-3xl">
                        &times;
                    </a>

                </div>

                <form action="{{ route('categories.update', $category->id) }}" method="POST" enctype="multipart/form-data"
                    class="p-6 space-y-5">

                    @csrf
                    @method('PATCH')

                    <div class="grid md:grid-cols-2 gap-6">

                        <!-- Name -->
                        <div>
                            <label class="block mb-2 font-medium text-gray-700">
                                Category Name
                            </label>

                            <input type="text" name="name" value="{{ old('name', $category->name) }}"
                                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500"
                                placeholder="Electronics">

                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div>
                            <label class="block mb-2 font-medium text-gray-700">
                                Slug
                            </label>

                            <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500"
                                placeholder="electronics">
                            @error('slug')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <!-- Description -->
                    <div class="mt-6">
                        <label class="block mb-2 font-medium text-gray-700">
                            Description
                        </label>

                        <textarea rows="3" name="description" class="w-full px-4 py-3 border rounded-xl resize-none"
                            placeholder="Write description...">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Parent Category -->
                    <div class="mt-6">
                        <label class="block mb-2 font-medium text-gray-700">
                            Parent Category <span class="text-gray-400 text-sm font-normal">(optional)</span>
                        </label>

                        @if ($parentCategories->isNotEmpty())
                            <div class="border rounded-xl overflow-hidden">
                                <div class="max-h-56 overflow-y-auto divide-y divide-gray-100">
                                    <label class="flex items-center px-4 py-3 cursor-pointer hover:bg-blue-50 transition-colors {{ old('parent_id', $category->parent_id) == '' ? 'bg-blue-50' : '' }}">
                                        <input type="radio" name="parent_id" value=""
                                            class="w-4 h-4 text-blue-600" @checked(old('parent_id', $category->parent_id) == '')>
                                        <span class="ml-3 text-gray-500 font-medium">— None (Top Level) —</span>
                                    </label>

                                    @foreach ($parentCategories as $parent)
                                        <label class="flex items-center px-4 py-3 cursor-pointer hover:bg-blue-50 transition-colors {{ old('parent_id', $category->parent_id) == $parent->id ? 'bg-blue-50' : '' }}">
                                            <input type="radio" name="parent_id" value="{{ $parent->id }}"
                                                class="w-4 h-4 text-blue-600" @checked(old('parent_id', $category->parent_id) == $parent->id)>
                                            <span class="ml-3 font-medium text-gray-800">{{ $parent->name }}</span>
                                        </label>

                                        @if ($parent->children && $parent->children->isNotEmpty())
                                            @foreach ($parent->children as $child)
                                                <label class="flex items-center px-4 py-3 pl-12 cursor-pointer hover:bg-blue-50 transition-colors {{ old('parent_id', $category->parent_id) == $child->id ? 'bg-blue-50' : '' }}">
                                                    <input type="radio" name="parent_id" value="{{ $child->id }}"
                                                        class="w-4 h-4 text-blue-600" @checked(old('parent_id', $category->parent_id) == $child->id)>
                                                    <span class="ml-3 text-gray-700">↳ {{ $child->name }}</span>
                                                </label>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic px-1">No parent categories available.</p>
                            <input type="hidden" name="parent_id" value="">
                        @endif

                        @error('parent_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror

                        <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Select a parent category to make this a subcategory (e.g., Ball → Football).
                        </p>
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block mb-2 font-medium text-gray-700">
                            Category Image <span class="text-gray-400 text-sm font-normal">(optional)</span>
                        </label>

                        @if ($category->image)
                            <div class="mb-4">
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                    class="w-32 h-32 object-cover rounded-lg">
                            </div>
                        @endif

                        <label
                            class="border-2 border-dashed rounded-xl p-6 text-center hover:bg-gray-50 cursor-pointer block"
                            onclick="this.querySelector('input[type=file]').click()">

                            <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16V4m0 0L3 8m4-4l4 4m6 8v4m0 0l-4-4m4 4l4-4" />
                            </svg>

                            <p class="mt-2 text-gray-500">
                                Click to upload image
                            </p>

                            <input type="file" name="image" class="hidden" accept="image/*"
                                onchange="this.closest('div').querySelector('p').textContent = this.files[0].name">
                        </label>

                        @error('image')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="mt-6 flex items-center gap-3">

                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="w-5 h-5">

                        <span class="text-gray-700">
                            Active Category
                        </span>

                    </div>

                    <!-- Footer -->
                    <div class="sticky bottom-0 bg-white border-t pt-4 flex justify-end gap-3">

                        <a href="{{ route('categories.index') }}"
                            class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700">Cancel</a>

                        <button class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">

                            Save Category

                        </button>

                    </div>

                </form>

            </div>

        </div>



    </div>

@endsection
