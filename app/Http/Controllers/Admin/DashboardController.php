<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // ── Overview counts ──
        $totalUsers      = User::count();
        $totalOrders     = Order::count();
        $totalCategories = Category::count();
        $totalProducts   = Product::count();
        $totalReviews    = Review::count();

        // ── Revenue ──
        $revenueToday = (float) Order::whereDate('created_at', today())->sum('total');
        $revenueMonth = (float) Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
        $revenueTotal = (float) Order::sum('total');

        $avgOrderValue = $totalOrders > 0 ? round($revenueTotal / $totalOrders, 2) : 0;

        // ── Pending / processing ──
        $pendingOrders    = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();

        // ── Low stock ──
        $lowStockProducts = Product::orderBy('stock')
            ->take(8)
            ->get(['id', 'name', 'stock', 'image', 'sku'])
            ->map(fn ($p) => [
                'id'        => $p->id,
                'name'      => $p->name,
                'sku'       => $p->sku,
                'stock'     => $p->stock,
                'image_url' => $p->image ? asset('storage/' . $p->image) : null,
            ]);

        // ── Order status distribution (doughnut) ──
        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── Daily revenue, last 7 days (bar) ──
        $dailyRevenue = Order::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as order_count, COALESCE(SUM(total), 0) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date    = now()->subDays($i)->format('Y-m-d');
            $dayData = $dailyRevenue->get($date);
            $chart[] = [
                'label'   => now()->subDays($i)->format('D'),
                'orders'  => $dayData ? (int) $dayData->order_count : 0,
                'revenue' => $dayData ? (float) $dayData->revenue : 0.0,
            ];
        }

        // ── Top selling products ──
        $topProducts = OrderItem::select('product_id')
            ->selectRaw('SUM(quantity) as total_qty')
            ->selectRaw('SUM(subtotal) as total_revenue')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get()
            ->filter(fn ($row) => $row->product)
            ->values()
            ->map(fn ($row) => [
                'id'            => $row->product->id,
                'name'          => $row->product->name,
                'image_url'     => $row->product->image ? asset('storage/' . $row->product->image) : null,
                'total_qty'     => (int) $row->total_qty,
                'total_revenue' => (float) $row->total_revenue,
            ]);

        // ── Top categories by revenue ──
        $topCategories = Category::select('categories.id', 'categories.name')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as total_revenue')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get()
            ->map(fn ($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'total_sold'    => (int) $c->total_sold,
                'total_revenue' => (float) $c->total_revenue,
            ]);

        // ── Recent orders ──
        $recentOrders = Order::with('user:id,name,email')
            ->latest()
            ->take(6)
            ->get(['id', 'user_id', 'order_number', 'status', 'total', 'created_at'])
            ->map(fn ($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'status'       => $o->status,
                'total'        => (float) $o->total,
                'created_at'   => $o->created_at->toDateTimeString(),
                'customer'     => $o->user?->only(['id', 'name', 'email']),
            ]);

        // ── Latest reviews ──
        $latestReviews = Review::with(['user:id,name', 'product:id,name,slug'])
            ->latest()
            ->take(4)
            ->get()
            ->map(fn ($r) => [
                'id'          => $r->id,
                'rating'      => $r->rating,
                'comment'     => \Illuminate\Support\Str::limit($r->comment, 90),
                'is_approved' => (bool) $r->is_approved,
                'user_name'   => $r->user?->name,
                'product'     => $r->product?->only(['id', 'name']),
            ]);

        return response()->json([
            'data' => [
                'stats' => [
                    'total_users'      => $totalUsers,
                    'total_orders'     => $totalOrders,
                    'total_categories' => $totalCategories,
                    'total_products'   => $totalProducts,
                    'total_reviews'    => $totalReviews,
                    'revenue_today'    => $revenueToday,
                    'revenue_month'    => $revenueMonth,
                    'revenue_total'    => $revenueTotal,
                    'avg_order_value'  => $avgOrderValue,
                    'pending_orders'   => $pendingOrders,
                    'processing_orders' => $processingOrders,
                ],
                'low_stock_products' => $lowStockProducts,
                'status_counts'      => $statusCounts,
                'chart'              => $chart,
                'top_products'       => $topProducts,
                'top_categories'     => $topCategories,
                'recent_orders'      => $recentOrders,
                'latest_reviews'     => $latestReviews,
            ],
        ]);
    }
}
