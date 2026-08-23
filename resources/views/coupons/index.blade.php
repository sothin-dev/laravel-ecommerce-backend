@extends('layouts.app')

@section('title', 'Coupons')
@section('page-title', 'Coupons')

@section('content')

<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Coupons</h1>
            <p class="text-sm text-gray-500">Create and manage discount coupons.</p>
        </div>
        <a href="{{ route('coupons.create') }}"
            class="px-5 py-3 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700">
            + Create Coupon
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-2xl shadow border p-4 flex flex-wrap items-end gap-4">
        <div>
            <label class="block mb-1 text-sm font-medium text-gray-600">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Coupon code"
                class="px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 w-56">
        </div>
        <div>
            <label class="block mb-1 text-sm font-medium text-gray-600">Status</label>
            <select name="status"
                class="px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500">
                <option value="">All</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
        </div>
        <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white rounded-xl hover:bg-gray-900">Filter</button>
        <a href="{{ route('coupons.index') }}" class="px-5 py-2.5 bg-gray-100 rounded-xl hover:bg-gray-200">Reset</a>
    </form>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow border overflow-hidden">

        @if ($coupons->count())

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Code</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Type</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Value</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Min. Order</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Usage</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Expires</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($coupons as $coupon)
                            @php
                                $expired = $coupon->expires_at && $coupon->expires_at->lessThan(now());
                            @endphp
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span class="font-mono font-semibold text-gray-800">{{ $coupon->code }}</span>
                                    @if ($coupon->description)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($coupon->description, 40) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600 capitalize">{{ $coupon->type }}</td>
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    @if ($coupon->type === 'percentage')
                                        {{ $coupon->value }}%
                                        @if ($coupon->max_discount)
                                            <span class="text-xs text-gray-400">max ${{ number_format($coupon->max_discount, 2) }}</span>
                                        @endif
                                    @else
                                        ${{ number_format($coupon->value, 2) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if ($coupon->min_order_amount > 0)
                                        ${{ number_format($coupon->min_order_amount, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $coupon->used_count }}
                                    @if ($coupon->usage_limit)
                                        / {{ $coupon->usage_limit }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $coupon->expires_at ? $coupon->expires_at->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if (! $coupon->is_active)
                                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">Inactive</span>
                                    @elseif ($expired)
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">Expired</span>
                                    @else
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Active</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('coupons.edit', $coupon->id) }}"
                                            class="px-4 py-2 border rounded-lg hover:bg-gray-100">Edit</a>
                                        <button type="button"
                                            onclick="openConfirmDelete('{{ route('coupons.destroy', $coupon->id) }}', 'Delete Coupon', 'Are you sure you want to delete coupon &quot;{{ $coupon->code }}&quot;? This action cannot be undone.')"
                                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50">
                {{ $coupons->links() }}
            </div>
        @else
            <div class="py-20 text-center">
                <div class="text-6xl mb-4">🏷️</div>
                <h2 class="text-xl font-bold text-gray-700">No Coupons Found</h2>
                <p class="mt-2 text-gray-500">Create your first coupon to start discounting orders.</p>
            </div>
        @endif

    </div>

</div>

@include('partials.confirm-modal')

@endsection
