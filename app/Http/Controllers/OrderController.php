<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index()
    {
        Order::where('user_id', auth()->id())
            ->where('order_status', 'shipped')
            ->whereNotNull('shipped_at')
            ->where('shipped_at', '<=', now()->subDays(3))
            ->update([
                'order_status' => 'completed',
                'completed_at' => now(),
            ]);

        $orders = Order::where('user_id', auth()->id())
            ->with(['items.product', 'items.variant'])
            ->latest()
            ->get();

        $totalOrders = $orders->count();
        $processingOrders = $orders->whereIn('order_status', ['pending', 'shipped'])->count();
        $completedOrders = $orders->where('order_status', 'completed')->count();

        return view('orders.index', compact(
            'orders',
            'totalOrders',
            'processingOrders',
            'completedOrders'
        ));
    }

    public function show($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->with(['items.product', 'items.variant'])
            ->findOrFail($id);

        return view('orders.show', compact('order'));
    }

    public function complete(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->order_status !== 'shipped') {
            return redirect()
                ->route('orders.index')
                ->with('error', 'Pesanan hanya bisa diselesaikan jika status pengiriman sudah Shipping.');
        }

        $order->update([
            'order_status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('orders.index')
            ->with('success', 'Pesanan berhasil diselesaikan.');
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($order->order_status, ['shipped', 'completed', 'cancelled'])) {
            return redirect()
                ->route('orders.index')
                ->with('error', 'Pesanan ini sudah tidak bisa dibatalkan.');
        }

        if ($order->created_at->lt(now()->subDay())) {
            return redirect()
                ->route('orders.index')
                ->with('error', 'Pesanan sudah melewati batas waktu pembatalan.');
        }

        $order->load(['items.product']);

        foreach ($order->items as $item) {
            if (!$item->product) {
                continue;
            }

            $stockToReturn = $item->quantity - ($item->waiting_restock_quantity ?? 0);

            if ($stockToReturn > 0) {
                $item->product->increment('stock_quantity', $stockToReturn);
            }
        }

        $message = 'Pesanan berhasil dibatalkan dan stok produk sudah dikembalikan.';

        if ($order->payment_method === 'qris' && $order->payment_status === 'paid') {
            $message .= ' Karena pembayaran QRIS sudah dibayar, silakan hubungi admin untuk proses refund.';
        }

        $order->update([
            'order_status' => 'cancelled',
        ]);

        return redirect()
            ->route('orders.index')
            ->with('success', $message);
    }

    public function invoice(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.product', 'items.variant', 'user']);

        return view('orders.invoice', compact('order'));
    }

    public function uploadProof(Request $request, $orderId)
{
    dd('uploadProof dipanggil', $request->all(), $request->hasFile('payment_proof'));
}
}