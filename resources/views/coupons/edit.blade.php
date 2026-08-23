@extends('layouts.app')

@section('title', 'Edit Coupon')
@section('page-title', 'Edit Coupon')

@section('content')

<div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-8 py-6 border-b">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Coupon</h1>
                <p class="text-sm text-gray-500 mt-1">Update the discount code settings.</p>
            </div>
            <a href="{{ route('coupons.index') }}" class="text-3xl text-gray-400 hover:text-red-500">&times;</a>
        </div>

        <form action="{{ route('coupons.update', $coupon->id) }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 font-medium text-gray-700">Coupon Code</label>
                    <input type="text" name="code" value="{{ old('code', $coupon->code) }}" placeholder="SUMMER25"
                        class="w-full px-4 py-3 border rounded-xl uppercase focus:ring-2 focus:ring-blue-500">
                    @error('code')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Discount Type</label>
                    <select name="type" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500">
                        <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                    </select>
                    @error('type')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Value</label>
                    <input type="number" step="0.01" name="value" value="{{ old('value', $coupon->value) }}" placeholder="10"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500">
                    @error('value')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Minimum Order ($)</label>
                    <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" placeholder="0"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500">
                    @error('min_order_amount')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Max Discount ($)</label>
                    <input type="number" step="0.01" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}" placeholder="Optional"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500">
                    @error('max_discount')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Usage Limit</label>
                    <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="Unlimited"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500">
                    @error('usage_limit')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Starts At</label>
                    <input type="date" name="starts_at" value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d') : '') }}"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500">
                    @error('starts_at')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block mb-2 font-medium text-gray-700">Expires At</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '') }}"
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500">
                    @error('expires_at')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block mb-2 font-medium text-gray-700">Description</label>
                <textarea name="description" rows="3"
                    class="w-full px-4 py-3 border rounded-xl resize-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Optional internal note">{{ old('description', $coupon->description) }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }} class="w-5 h-5">
                <span class="text-gray-700">Active</span>
            </div>

            <div class="border-t pt-5 flex justify-end gap-3">
                <a href="{{ route('coupons.index') }}" class="px-6 py-3 bg-gray-200 rounded-xl hover:bg-gray-300">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

@endsection
