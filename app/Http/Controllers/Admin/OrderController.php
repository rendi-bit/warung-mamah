<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with([
            'user',
            'shippingArea'
        ])
        ->where('order_status', '!=', 'waiting_payment')
        ->latest()
        ->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user','shippingArea','items.product','items.variant']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed',
            'order_status'   => 'required|in:waiting_payment,waiting_confirmation,processed,shipped,completed,cancelled',
        ]);

        if ($order->order_status === 'completed') {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('info', 'Pesanan sudah selesai dan tidak bisa diubah kembali.');
        }

        if ($request->order_status === 'shipped' && $order->has_waiting_restock) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('error', 'Pesanan belum bisa dikirim karena masih ada item yang menunggu restok.');
        }

        $newStatus = $request->order_status;
        $data      = [
            'payment_status' => $request->payment_status,
            'order_status'   => $newStatus,
        ];

        if ($newStatus === 'shipped' && !$order->shipped_at) {
            $data['shipped_at'] = now();
        }

        if ($newStatus === 'completed' && !$order->completed_at) {
            $data['completed_at'] = now();
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
                ->with('info', 'Pembayaran sudah dikonfirmasi sebelumnya.');
        }

        $order->update([
            'payment_status'       => 'paid',
            'order_status'         => 'processed',
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
                ->with('info', 'Pesanan ini sudah tidak memiliki item yang menunggu restok.');
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
            ->with('success', 'Semua restok terpenuhi. Pesanan sekarang bisa diproses ke Shipping.');
    }

    /**
     * ✅ NEW: Restok per item langsung dari tabel pesanan
     * Admin klik tombol "Restok" di baris item → stok produk bertambah
     */
    public function restockItem(Order $order, OrderItem $item)
    {
        // Pastikan item milik order ini
        if ($item->order_id !== $order->id) {
            abort(403, 'Akses ditolak.');
        }

        if (!$item->is_waiting_restock) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('info', 'Item ini sudah tidak menunggu restok.');
        }

        $product = $item->product;

        if (!$product) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('error', 'Produk tidak ditemukan.');
        }

        // Tambah stok sebesar waiting_restock_quantity
        $restockQty = $item->waiting_restock_quantity ?? 0;

        if ($restockQty > 0) {
            $product->increment('stock_quantity', $restockQty);
        }

        // Update item — tandai sudah direstok
        $item->update([
            'is_waiting_restock'       => false,
            'waiting_restock_quantity' => 0,
        ]);

        // Cek apakah masih ada item lain yang waiting restock
        $stillWaiting = $order->items()
            ->where('is_waiting_restock', true)
            ->exists();

        if (!$stillWaiting) {
            $order->update([
                'has_waiting_restock' => false,
                'restock_note'        => null,
            ]);
        }

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Stok produk "' . $product->name . '" berhasil direstok sebanyak ' . $restockQty . ' ' . ($product->stock_unit ?? 'item') . '.');
    }
}