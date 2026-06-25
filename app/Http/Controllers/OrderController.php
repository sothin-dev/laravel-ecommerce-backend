<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')
            ->latest()
            ->paginate(10);
        return view('orders.list', compact('orders'));
    }

    public function show(string $id)
    {
        $order = Order::with(['user', 'items.product'])
            ->findOrFail($id);

        return view('orders.show', compact('order'));
    }
}
