<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $shippedOrders = Order::where('user_id', Auth::id())
            ->where('order_status', 'shipped')
            ->whereNotNull('shipped_at')
            ->where('shipped_at', '<=', now()->subDays(3))
            ->get();

        foreach ($shippedOrders as $o) {
            $o->update([
                'order_status' => 'completed',
                'completed_at' => $o->shipped_at->addDays(3),
            ]);
        }

        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product', 'items.variant'])
            ->latest()
            ->paginate(10);

        $totalOrders = Order::where('user_id', Auth::id())->count();

        $processingOrders = Order::where('user_id', Auth::id())
            ->whereIn('order_status', ['pending', 'processing', 'shipped'])
            ->count();

        $completedOrders = Order::where('user_id', Auth::id())
            ->where('order_status', 'completed')
            ->count();

        return view('orders.index', compact(
            'orders',
            'totalOrders',
            'processingOrders',
            'completedOrders'
        ));
    }

    public function show($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->with(['items.product', 'items.variant'])
            ->findOrFail($id);

        return view('orders.show', compact('order'));
    }

    public function complete(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->order_status !== 'shipped') {
            return redirect()->route('orders.index')
                ->with('error', 'Pesanan hanya bisa diselesaikan jika status pengiriman sudah Shipped.');
        }

        $order->update([
            'order_status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan berhasil diselesaikan. Terima kasih!');
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (in_array($order->order_status, ['shipped', 'completed', 'cancelled'])) {
            return redirect()->route('orders.index')
                ->with('error', 'Pesanan ini sudah tidak bisa dibatalkan.');
        }

        if ($order->created_at->lt(now()->subDay())) {
            return redirect()->route('orders.index')
                ->with('error', 'Pesanan sudah melewati batas waktu pembatalan (1x24 jam).');
        }

        DB::transaction(function () use ($order) {
            $order->load(['items.product', 'items.variant']);

            foreach ($order->items as $item) {
                if (!$item->product) continue;

                $stockToReturn = $item->quantity - ($item->waiting_restock_quantity ?? 0);

                if ($stockToReturn <= 0) {
                    continue;
                }

                // ✅ FIX FINAL: Keduanya mengembalikan ke stock_quantity produk utama
                // Tidak perlu lagi memisahkan rumus base_stock gram perkalian berat.
                $product = $item->product;
                $product->lockForUpdate();
                $product->increment('stock_quantity', $stockToReturn);
            }

            $order->update(['order_status' => 'cancelled']);
        });

        $message = 'Pesanan berhasil dibatalkan dan stok produk sudah dikembalikan.';

        if ($order->payment_method === 'qris' && $order->payment_status === 'paid') {
            $message .= ' Pembayaran QRIS sudah terkonfirmasi — silakan hubungi admin via WhatsApp untuk proses refund.';
        }

        return redirect()->route('orders.index')->with('success', $message);
    }

    public function invoice(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'items.variant', 'user']);

        return view('orders.invoice', compact('order'));
    }

    public function uploadProof(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($order->payment_method !== 'qris') {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Hanya pesanan QRIS yang memerlukan bukti pembayaran.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order->id)
                ->with('info', 'Pembayaran sudah dikonfirmasi. Tidak perlu upload ulang.');
        }

        if (in_array($order->order_status, ['cancelled', 'completed'])) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Pesanan ini sudah ' . $order->order_status . '. Bukti pembayaran tidak bisa diupload.');
        }

        $request->validate([
            'payment_proof' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],
        ], [
            'payment_proof.required' => 'Bukti pembayaran wajib diupload.',
            'payment_proof.file'     => 'Upload harus berupa file.',
            'payment_proof.image'    => 'File harus berupa gambar.',
            'payment_proof.mimes'    => 'Format gambar harus JPG, JPEG, atau PNG.',
            'payment_proof.max'      => 'Ukuran gambar maksimal 2MB.',
        ]);

        if ($order->payment_proof && Storage::disk('public')->exists($order->payment_proof)) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        Storage::disk('public')->makeDirectory('payment_proofs');

        $extension = $request->file('payment_proof')->getClientOriginalExtension();
        $filename  = 'proof_' . $order->order_code . '_' . Str::random(8) . '_' . time() . '.' . $extension;

        $path = $request->file('payment_proof')->storeAs(
            'payment_proofs',
            $filename,
            'public'
        );

        if (!$path) {
            return back()->with('error', 'Gagal menyimpan file. Silakan coba lagi.');
        }

        $order->update([
            'payment_proof' => $path,
            'payment_status' => 'pending',
            'order_status' => 'waiting_confirmation',
            'need_reupload' => false,
            'reupload_note' => null,
        ]);

        return redirect()->route('orders.invoice', $order->id)
            ->with('success', 'Bukti pembayaran berhasil diupload! Admin akan segera memverifikasi.');
    }
}