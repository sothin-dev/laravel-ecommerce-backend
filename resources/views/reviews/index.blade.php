@extends('layouts.app')

@section('title', 'Reviews')
@section('page-title', 'Reviews')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Reviews</h1>
            <p class="text-sm text-gray-500">Moderate customer product reviews.</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-2xl shadow border p-4 flex flex-wrap items-end gap-4">
        <div>
            <label class="block mb-1 text-sm font-medium text-gray-600">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Comment, customer or product"
                class="px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 w-72">
        </div>
        <div>
            <label class="block mb-1 text-sm font-medium text-gray-600">Status</label>
            <select name="status" class="px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500">
                <option value="">All</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </div>
        <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white rounded-xl hover:bg-gray-900">Filter</button>
        <a href="{{ route('reviews.index') }}" class="px-5 py-2.5 bg-gray-100 rounded-xl hover:bg-gray-200">Reset</a>
    </form>

    <div class="bg-white rounded-2xl shadow border overflow-hidden">

        @if ($reviews->count())

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Product</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Customer</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Rating</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Comment</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reviews as $review)
                            <tr class="border-b hover:bg-gray-50 align-top">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-800">{{ $review->product->name ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $review->user->name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}</span>
                                    <span class="text-gray-300">{{ str_repeat('★', 5 - $review->rating) }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 max-w-xs">
                                    {{ $review->comment ? Str::limit($review->comment, 80) : '<em class="text-gray-400">No comment</em>' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($review->is_approved)
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Approved</span>
                                    @else
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        @if (! $review->is_approved)
                                            <form action="{{ route('reviews.approve', $review->id) }}" method="POST">
                                                @csrf
                                                <button class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">Approve</button>
                                            </form>
                                        @else
                                            <form action="{{ route('reviews.hide', $review->id) }}" method="POST">
                                                @csrf
                                                <button class="px-3 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 text-sm">Hide</button>
                                            </form>
                                        @endif
                                        <button type="button"
                                            onclick="openConfirmDelete('{{ route('reviews.destroy', $review->id) }}', 'Delete Review', 'Are you sure you want to delete this review? This action cannot be undone.')"
                                            class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50">
                {{ $reviews->links() }}
            </div>
        @else
            <div class="py-20 text-center">
                <div class="text-6xl mb-4">⭐</div>
                <h2 class="text-xl font-bold text-gray-700">No Reviews Found</h2>
                <p class="mt-2 text-gray-500">Customer reviews will appear here for moderation.</p>
            </div>
        @endif

    </div>

</div>

@include('partials.confirm-modal')

@endsection
