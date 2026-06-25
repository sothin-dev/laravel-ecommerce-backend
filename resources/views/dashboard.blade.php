@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Welcome Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome back, Admin</h1>
        <p class="text-sm text-gray-500 mt-1">{{ now()->format('l, F j, Y') }} &bull; Here&rsquo;s your store performance snapshot.</p>
    </div>
    <div class="mt-4 sm:mt-0 flex items-center gap-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            {{ $totalOrders }} total orders
        </span>
        @if ($pendingOrders > 0)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                {{ $pendingOrders }} pending
            </span>
        @endif
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">

    {{-- Revenue Today --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue Today</span>
            <div class="p-1.5 rounded-lg bg-emerald-50">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">${{ number_format($revenueToday, 2) }}</p>
        <p class="text-xs text-gray-400 mt-1">Month: ${{ number_format($revenueMonth, 2) }}</p>
    </div>

    {{-- Total Orders --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Orders</span>
            <div class="p-1.5 rounded-lg bg-blue-50">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalOrders) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $processingOrders }} processing &bull; {{ $pendingOrders }} pending</p>
    </div>

    {{-- Total Users --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Users</span>
            <div class="p-1.5 rounded-lg bg-indigo-50">
                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
        <p class="text-xs text-gray-400 mt-1">Registered customers</p>
    </div>

    {{-- Total Products --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Products</span>
            <div class="p-1.5 rounded-lg bg-amber-50">
                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalProducts) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $totalCategories }} categories</p>
    </div>

    {{-- Avg Order Value --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Avg. Order</span>
            <div class="p-1.5 rounded-lg bg-purple-50">
                <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75H17.25m0 0v-3m0 3v3" />
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">${{ number_format($avgOrderValue, 2) }}</p>
        <p class="text-xs text-gray-400 mt-1">Per order average</p>
    </div>

    {{-- Reviews --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Reviews</span>
            <div class="p-1.5 rounded-lg bg-pink-50">
                <svg class="w-4 h-4 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalReviews) }}</p>
        <p class="text-xs text-gray-400 mt-1">Customer feedback</p>
    </div>

</div>
{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Orders & Revenue Chart --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-semibold text-gray-900">Orders &amp; Revenue &mdash; Last 7 Days</h3>
            <div class="flex items-center gap-4 text-xs">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm bg-indigo-500"></span> Orders
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm bg-emerald-400"></span> Revenue
                </span>
            </div>
        </div>
        <div class="h-64">
            <canvas id="ordersChart"></canvas>
        </div>
    </div>

    {{-- Order Status Doughnut --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-5">Order Status</h3>
        <div class="h-56 flex items-center justify-center">
            <canvas id="statusChart"></canvas>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
            @php
                $statusColors = [
                    'pending' => ['bg-amber-500', 'text-amber-700'],
                    'processing' => ['bg-blue-500', 'text-blue-700'],
                    'completed' => ['bg-emerald-500', 'text-emerald-700'],
                    'cancelled' => ['bg-red-500', 'text-red-700'],
                ];
                $statusLabels = [
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ];
            @endphp
            @foreach ($statusLabels as $key => $label)
                @php $count = $statusCounts[$key] ?? 0; @endphp
                <div class="flex items-center gap-2 p-1.5 rounded-lg {{ $count > 0 ? 'bg-gray-50' : '' }}">
                    <span class="w-2.5 h-2.5 rounded-full {{ $statusColors[$key][0] }}"></span>
                    <span class="text-gray-600">{{ $label }}</span>
                    <span class="ml-auto font-semibold {{ $statusColors[$key][1] }}">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>

</div>
{{-- Tables Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Recent Orders --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 lg:col-span-2 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Recent Orders</h3>
            <a href="{{ route('orders.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">View All &rarr;</a>
        </div>
        @if ($recentOrders->count())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Order</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Customer</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Status</th>
                            <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recentOrders as $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('orders.show', $order->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-700">{{ $order->user->name }}</td>
                                <td class="px-5 py-3.5">
                                    @if ($order->status == 'pending')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending</span>
                                    @elseif($order->status == 'processing')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Processing</span>
                                    @elseif($order->status == 'completed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Completed</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Cancelled</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-800 font-medium text-right">${{ number_format($order->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-12 text-center"><p class="text-gray-400 text-sm">No orders yet.</p></div>
        @endif
    </div>

    {{-- Top Selling Products --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-900">Top Selling Products</h3></div>
        @if ($topProducts->count())
            <div class="divide-y divide-gray-100">
                @foreach ($topProducts as $item)
                    <div class="px-5 py-3 flex items-center gap-3">
                        @if ($item->product && $item->product->image)
                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $item->product?->name ?? 'Deleted Product' }}</p>
                            <p class="text-xs text-gray-400">{{ $item->total_qty }} sold &bull; ${{ number_format($item->total_revenue, 2) }}</p>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600">#{{ $loop->iteration }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-10 text-center"><p class="text-gray-400 text-sm">No sales yet.</p></div>
        @endif
    </div>

</div>
{{-- Alerts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Low Stock --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Low Stock Alerts</h3>
            @if ($lowStockCount > 0)
                <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full">{{ $lowStockCount }} items</span>
            @endif
        </div>
        @if ($lowStockProducts->count())
            <div class="divide-y divide-gray-100">
                @foreach ($lowStockProducts as $product)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="" class="w-9 h-9 rounded-lg object-cover flex-shrink-0">
                            @else
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                </div>
                            @endif
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $product->name }}</p>
                        </div>
                        <span class="text-xs font-bold {{ $product->stock == 0 ? 'text-red-600' : 'text-amber-600' }} ml-3">{{ $product->stock }} left</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-10 text-center">
                <svg class="w-8 h-8 text-emerald-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="text-gray-400 text-sm">All products well stocked.</p>
            </div>
        @endif
    </div>

    {{-- Top Categories --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-900">Top Categories by Revenue</h3></div>
        @if ($topCategories->count())
            <div class="px-5 py-4 space-y-4">
                @foreach ($topCategories as $cat)
                    @php $pct = round(($cat->total_revenue / $topCategoryMax) * 100); @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-gray-700">{{ $cat->name }}</span>
                            <span class="text-gray-500">${{ number_format($cat->total_revenue, 2) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-400 transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-10 text-center"><p class="text-gray-400 text-sm">No sales data yet.</p></div>
        @endif
    </div>

    {{-- Latest Reviews --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-900">Latest Reviews</h3></div>
        @if ($latestReviews->count())
            <div class="divide-y divide-gray-100">
                @foreach ($latestReviews as $review)
                    <div class="px-5 py-3">
                        <div class="flex items-center gap-1 mb-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            @endfor
                        </div>
                        <p class="text-sm text-gray-700 line-clamp-2">&ldquo;{{ $review->comment }}&rdquo;</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $review->user?->name ?? 'Anonymous' }} on {{ $review->product?->name ?? 'Deleted Product' }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-10 text-center"><p class="text-gray-400 text-sm">No reviews yet.</p></div>
        @endif
    </div>

</div>
{{-- @endsection --}}
{{-- Chart Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Orders & Revenue Bar + Line Chart
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ordersCtx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'Orders',
                    data: @json($chartOrders),
                    backgroundColor: 'rgba(99, 102, 241, 0.85)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 2
                },
                {
                    label: 'Revenue ($)',
                    data: @json($chartRevenue),
                    type: 'line',
                    borderColor: '#34d399',
                    backgroundColor: 'rgba(52, 211, 153, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#34d399',
                    pointHoverRadius: 6,
                    yAxisID: 'y1',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.datasetIndex === 0) return 'Orders: ' + ctx.parsed.y;
                            return 'Revenue: $' + ctx.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#9ca3af', font: { size: 11 } },
                    grid: { color: '#f3f4f6' }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { display: false },
                    ticks: {
                        color: '#9ca3af',
                        font: { size: 11 },
                        callback: function(value) { return '$' + value; }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#9ca3af', font: { size: 11 } }
                }
            }
        }
    });

    // Order Status Doughnut
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: @json($statusCounts->keys()->map(fn($s) => ucfirst($s))->values()),
            datasets: [{
                data: @json($statusCounts->values()),
                backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false }
            }
        }
    });

});
</script>

@endsection