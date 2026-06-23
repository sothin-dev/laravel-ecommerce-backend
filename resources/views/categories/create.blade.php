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
                            Create Category
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Fill in the information below to create a new category.
                        </p>
                    </div>

                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-red-500 text-3xl">
                        &times;
                    </button>

                </div>

                <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data"
                    class="p-6 space-y-5">

                    @csrf

                    <div class="grid md:grid-cols-2 gap-6">

                        <!-- Name -->
                        <div>
                            <label class="block mb-2 font-medium text-gray-700">
                                Category Name
                            </label>

                            <input type="text" name="name" value="{{ old('name') }}"
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

                            <input type="text" name="slug" value="{{ old('slug') }}"
                                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500"
                                placeholder="electronics">
                        </div>

                    </div>

                    <!-- Description -->
                    <div class="mt-6">
                        <label class="block mb-2 font-medium text-gray-700">
                            Description
                        </label>

                        <textarea rows="3" name="description" class="w-full px-4 py-3 border rounded-xl resize-none"
                            placeholder="Write description...">{{ old('description') }}</textarea>
                    </div>

                    <!-- Image -->
                    <label class="block">
                        <span class="block mb-2 font-medium text-gray-700">
                            Category Image
                        </span>

                        <div class="border-2 border-dashed rounded-xl p-6 text-center hover:bg-gray-50 cursor-pointer">

                            <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16V4m0 0L3 8m4-4l4 4m6 8v4m0 0l-4-4m4 4l4-4" />
                            </svg>

                            <p class="mt-2 text-gray-500">
                                Click to upload image
                            </p>

                            <input type="file" name="image" class="hidden">
                        </div>
                    </label>

                    <!-- Status -->
                    <div class="mt-6 flex items-center gap-3">

                        <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5">

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
