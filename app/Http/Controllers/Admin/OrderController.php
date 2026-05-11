<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // ✅ FIX: Pakai paginate(15) agar tidak loading semua data sekaligus
        $orders = Order::with('user')->latest()->paginate(15);

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
            'order_status'   => 'required|in:pending,shipped,completed',
        ]);

        if ($order->order_status === 'completed') {
            $order->update([
                'payment_status' => $request->payment_status,
                'order_status'   => 'completed',
                'completed_at'   => $order->completed_at ?? now(),
            ]);

            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('success', 'Pesanan sudah selesai dan tidak diubah kembali.');
        }

        if ($request->order_status === 'shipped' && $order->has_waiting_restock) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('error', 'Pesanan belum bisa dikirim karena masih ada item yang menunggu restok.');
        }

        $newStatus = $request->order_status;

        $data = [
            'payment_status' => $request->payment_status,
            'order_status'   => $newStatus,
        ];

        if ($newStatus === 'shipped' && !$order->shipped_at) {
            $data['shipped_at'] = now();
        }

        if ($newStatus === 'pending') {
            $data['shipped_at']   = null;
            $data['completed_at'] = null;
        }

        $order->update($data);

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function confirmPayment(Order $order)
    {
        if ($order->payment_status === 'paid') {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('success', 'Pembayaran sudah dikonfirmasi sebelumnya.');
        }

        $order->update([
            'payment_status'       => 'paid',
            'payment_confirmed_at' => now(),
        ]);

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }

    public function fulfillRestock(Order $order)
    {
        if (!$order->has_waiting_restock) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('success', 'Pesanan ini sudah tidak memiliki item yang menunggu restok.');
        }

        foreach ($order->items as $item) {
            if ($item->is_waiting_restock) {
                $item->update([
                    'is_waiting_restock'       => false,
                    'waiting_restock_quantity' => 0,
                ]);
            }
        }

        $order->update([
            'has_waiting_restock' => false,
            'restock_note'        => null,
        ]);

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Restok pesanan sudah ditandai terpenuhi. Pesanan sekarang bisa diproses ke Shipping.');
    }
}
