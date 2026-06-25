<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class authController extends Controller
{
    /**
     * login form
     */
    public function showlogin()
    {
        return view('loginForm');
    }


    /**
     * Login for admin
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        
        if (Auth::guard('admin')->attempt($credentials)) {

            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ]);
    }

    /**
     * logout
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function dashboard()
    {
        // ── Overview Counts ──
        $totalUsers      = User::count();
        $totalOrders     = Order::count();
        $totalCategories = Category::count();
        $totalProducts   = Product::count();
        $totalReviews    = Review::count();

        // ── Revenue ──
        $revenueToday  = (float) Order::whereDate('created_at', today())->sum('total');
        $revenueMonth  = (float) Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
        $revenueTotal  = (float) Order::sum('total');

        // ── Average Order Value ──
        $avgOrderValue = $totalOrders > 0 ? $revenueTotal / $totalOrders : 0;

        // ── Pending / Processing Orders ──
        $pendingOrders    = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();

        // ── Low Stock Products ──
        $lowStockProducts = Product::where('stock', '<=', 5)
            ->orderBy('stock')
            ->take(8)
            ->get(['id', 'name', 'stock', 'image']);
        $lowStockCount = $lowStockProducts->count();

        // ── Order Status Distribution (for doughnut chart) ──
        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── Daily Revenue (last 7 days) for bar chart ──
        $dailyRevenue = Order::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as order_count, COALESCE(SUM(total), 0) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartOrders = [];
        $chartRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date           = now()->subDays($i)->format('Y-m-d');
            $label          = now()->subDays($i)->format('D');
            $dayData        = $dailyRevenue->get($date);
            $chartLabels[]  = $label;
            $chartOrders[]  = $dayData ? (int) $dayData->order_count : 0;
            $chartRevenue[] = $dayData ? (float) $dayData->revenue : 0;
        }

        // ── Top Selling Products ──
        $topProducts = OrderItem::select('product_id')
            ->selectRaw('SUM(quantity) as total_qty')
            ->selectRaw('SUM(subtotal) as total_revenue')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // ── Top Categories by revenue ──
        $topCategories = Category::select('categories.id', 'categories.name')
            ->selectRaw('COALESCE(SUM(order_items.subtotal), 0) as total_revenue')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        $topCategoryMax = $topCategories->max('total_revenue') ?: 1;

        // ── Recent Orders ──
        $recentOrders = Order::with('user')
            ->latest()
            ->take(6)
            ->get();

        // ── Latest Reviews ──
        $latestReviews = Review::with(['user', 'product'])
            ->latest()
            ->take(4)
            ->get();

        return view('dashboard', compact(
            'totalUsers',
            'totalOrders',
            'totalCategories',
            'totalProducts',
            'totalReviews',
            'revenueToday',
            'revenueMonth',
            'revenueTotal',
            'avgOrderValue',
            'pendingOrders',
            'processingOrders',
            'lowStockProducts',
            'lowStockCount',
            'statusCounts',
            'chartLabels',
            'chartOrders',
            'chartRevenue',
            'topProducts',
            'topCategories',
            'topCategoryMax',
            'recentOrders',
            'latestReviews',
        ));
    }
}
