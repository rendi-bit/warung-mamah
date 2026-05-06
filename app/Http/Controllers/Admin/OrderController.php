<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->latest()->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'items.variant']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
{
    $request->validate([
        'payment_status' => 'required|in:pending,paid,failed',
        'order_status' => 'required|in:pending,shipped,completed',
    ]);

    if ($order->order_status === 'completed') {
        $order->update([
            'payment_status' => $request->payment_status,
            'order_status' => 'completed',
            'completed_at' => $order->completed_at ?? now(),
        ]);

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Pesanan sudah selesai dan tidak diubah kembali.');
    }

    $newStatus = $request->order_status;

    $data = [
        'payment_status' => $request->payment_status,
        'order_status' => $newStatus,
    ];

    if ($newStatus === 'shipped' && !$order->shipped_at) {
        $data['shipped_at'] = now();
    }

    if ($newStatus === 'pending') {
        $data['shipped_at'] = null;
        $data['completed_at'] = null;
    }

    $order->update($data);

    return redirect()
        ->route('admin.orders.show', $order->id)
        ->with('success', 'Status pesanan berhasil diperbarui.');
    }
}