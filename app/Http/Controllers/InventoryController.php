<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')
            ->withSum('orderItems', 'quantity')
            ->orderBy('stock')
            ->paginate(15);

        return view('inventory.index', compact('products'));
    }
}
