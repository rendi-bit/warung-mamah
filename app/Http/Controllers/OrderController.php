<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
                        ->latest()
                        ->get();

        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with('items.product', 'items.variant')
                        ->where('user_id', auth()->id())
                        ->findOrFail($id);

        return view('orders.show', compact('order'));
    }
}