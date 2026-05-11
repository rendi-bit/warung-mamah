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

    public function uploadProof(Request $request, $orderId)
{
    $order = Order::findOrFail($orderId);

    if ($order->user_id !== auth()->id()) {
        abort(403);
    }

    $request->validate([
        'payment_proof' => 'required|image|mimes:jpg,jpeg,png,jfif|max:2048',
    ]);

    if (!$request->hasFile('payment_proof')) {

        return back()->with(
            'error',
            'File bukti pembayaran tidak ditemukan.'
        );
    }

    if ($order->payment_proof) {

        Storage::disk('public')->delete(
            $order->payment_proof
        );
    }

    $file = $request->file('payment_proof');

    $filename = time() . '_' . $file->getClientOriginalName();

    $path = $file->storeAs(
        'payment_proofs',
        $filename,
        'public'
    );

    $order->update([
        'payment_proof' => $path,
    ]);

    return back()->with(
        'success',
        'Bukti pembayaran berhasil diupload.'
    );
}
}