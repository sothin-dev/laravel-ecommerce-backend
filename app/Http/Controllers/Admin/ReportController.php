<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $days = min((int) $request->get('days', 30), 365);
        $start = now()->subDays($days - 1)->startOfDay();

        $orders = Order::where('created_at', '>=', $start)->where('status', '!=', 'cancelled');

        $revenue  = (float) (clone $orders)->sum('total');
        $orderCnt = (clone $orders)->count();

        // Daily series
        $daily = Order::where('created_at', '>=', $start)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, COALESCE(SUM(total),0) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($r) => $r->date);

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d        = now()->subDays($i);
            $row      = $daily->get($d->format('Y-m-d'));
            $series[] = [
                'label'   => $d->format('M j'),
                'orders'  => $row ? (int) $row->orders : 0,
                'revenue' => $row ? (float) $row->revenue : 0.0,
            ];
        }

        // Status breakdown
        $statusBreakdown = Order::where('created_at', '>=', $start)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Top products
        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.created_at', '>=', $start)
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('order_items.product_id')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->with('product:id,name,image')
            ->get()
            ->filter(fn ($r) => $r->product)
            ->values()
            ->map(fn ($r) => [
                'id'            => $r->product->id,
                'name'          => $r->product->name,
                'image_url'     => $r->product->image ? asset('storage/' . $r->product->image) : null,
                'total_qty'     => (int) $r->total_qty,
                'total_revenue' => (float) $r->total_revenue,
            ]);

        // Top categories
        $topCategories = Category::query()
            ->selectRaw('categories.id, categories.name,
                COALESCE(SUM(CASE WHEN orders.status != "cancelled" AND orders.created_at >= ? THEN order_items.quantity ELSE 0 END), 0) as total_sold,
                COALESCE(SUM(CASE WHEN orders.status != "cancelled" AND orders.created_at >= ? THEN order_items.subtotal ELSE 0 END), 0) as total_revenue', [$start, $start])
            ->leftJoin('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->take(8)
            ->get()
            ->map(fn ($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'total_sold'    => (int) $c->total_sold,
                'total_revenue' => (float) $c->total_revenue,
            ]);

        // New customers in range
        $newCustomers = User::where('created_at', '>=', $start)->count();
        $repeatCustomers = Order::where('created_at', '>=', $start)
            ->where('status', '!=', 'cancelled')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        return response()->json([
            'data' => [
                'days'             => $days,
                'revenue'          => $revenue,
                'orders_count'     => $orderCnt,
                'avg_order_value'  => $orderCnt > 0 ? round($revenue / $orderCnt, 2) : 0,
                'series'           => $series,
                'status_breakdown' => $statusBreakdown,
                'top_products'     => $topProducts,
                'top_categories'   => $topCategories,
                'new_customers'    => $newCustomers,
                'repeat_customers' => $repeatCustomers,
            ],
        ]);
    }
}
