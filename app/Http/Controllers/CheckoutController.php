<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Generate kode order unik 5 karakter.
     * Menggunakan loop dengan batas maksimal untuk mencegah infinite loop.
     */
    private function generateShortOrderCode(): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $code = strtoupper(Str::random(5));
            $attempt++;

            if ($attempt >= $maxAttempts) {
                // Fallback: tambahkan timestamp agar pasti unik
                $code = strtoupper(Str::random(3)) . substr(time(), -2);
                break;
            }
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }

    /**
     * Pesan error stok yang informatif untuk user.
     */
    private function stockErrorMessage($product): string
    {
        return 'Maaf, stok ' . $product->name . ' saat ini tidak mencukupi. ' .
            'Stok tersedia: ' . $product->stock_quantity . ' ' . ($product->stock_unit ?? 'pcs') . '. ' .
            'Diperkirakan restok dalam ' . ($product->restock_estimation ?? '1-2 hari') . '.';
    }

    /**
     * Halaman checkout — validasi keranjang sebelum tampil.
     */
    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())
            ->with(['items.product', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang belanja kosong.');
        }

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (!$product) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', 'Ada produk yang tidak ditemukan. Silakan perbarui keranjang Anda.');
            }

            if (!$item->is_waiting_restock && $item->quantity > $product->stock_quantity) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', $this->stockErrorMessage($product));
            }
        }

        $lastOrder = Order::where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('checkout.index', compact('cart', 'lastOrder'));
    }

    /**
     * Proses checkout — semua validasi stok & pengurangan stok di dalam transaction.
     * Menggunakan lockForUpdate() untuk mencegah race condition.
     */
    public function process(Request $request)
    {
        $request->validate([
            'shipping_address'  => 'required|string|max:1000',
            'customer_whatsapp' => 'required|string|max:30',
            'house_landmark'    => 'nullable|string|max:255',
            'notes'             => 'nullable|string|max:1000',
            'delivery_method'   => 'required|in:ojek_toko,ambil_di_toko',
            'payment_method'    => 'required|in:qris,cod',
        ]);

        $cart = Cart::where('user_id', auth()->id())
            ->with(['items.product', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang belanja kosong.');
        }

        // Hitung subtotal dari data keranjang saat ini
        $subtotal = 0;
        foreach ($cart->items as $item) {
            $price    = $item->variant ? $item->variant->price : $item->product->price;
            $subtotal += $price * $item->quantity;
        }

        $shippingCost = $request->delivery_method === 'ojek_toko' ? 10000 : 0;
        $grandTotal   = $subtotal + $shippingCost;
        $orderCode    = $this->generateShortOrderCode();

        $hasWaitingRestock = $cart->items->contains('is_waiting_restock', true);
        $restockNote       = $hasWaitingRestock ? 'Pesanan memiliki item yang menunggu restok.' : null;

        try {
            if ($request->payment_method === 'qris') {

                session([
                    'checkout_data' => [
                        'shipping_address'  => $request->shipping_address,
                        'customer_whatsapp' => $request->customer_whatsapp,
                        'house_landmark'    => $request->house_landmark,
                        'notes'             => $request->notes,
                        'delivery_method'   => $request->delivery_method,
                        'payment_method'    => $request->payment_method,
                    ]
                ]);

                return redirect()->route('checkout.payment.temp');
            }
            try {

                DB::transaction(function () use (
                    $request,
                    $cart,
                    $subtotal,
                    $shippingCost,
                    $grandTotal,
                    $orderCode,
                    $hasWaitingRestock,
                    $restockNote
                ) {

                    foreach ($cart->items as $item) {

                        $product = Product::lockForUpdate()
                            ->find($item->product_id);

                        if (!$product) {
                            throw new \Exception('Produk tidak ditemukan.');
                        }

                        if (
                            !$item->is_waiting_restock &&
                            $item->quantity > $product->stock_quantity
                        ) {
                            throw new \Exception(
                                $this->stockErrorMessage($product)
                            );
                        }

                        $stockToReduce = min(
                            $item->quantity,
                            $product->stock_quantity
                        );

                        if ($stockToReduce > 0) {
                            $product->decrement(
                                'stock_quantity',
                                $stockToReduce
                            );
                        }
                    }

                    $order = Order::create([
                        'order_code'          => $orderCode,
                        'user_id'             => auth()->id(),
                        'subtotal'            => $subtotal,
                        'shipping_cost'       => $shippingCost,
                        'discount_amount'     => 0,
                        'grand_total'         => $grandTotal,
                        'payment_method'      => 'cod',
                        'payment_status'      => 'pending',
                        'order_status'        => 'processed',
                        'shipping_address'    => $request->shipping_address,
                        'customer_whatsapp'   => $request->customer_whatsapp,
                        'house_landmark'      => $request->house_landmark,
                        'delivery_method'     => $request->delivery_method,
                        'notes'               => $request->notes,
                        'has_waiting_restock' => $hasWaitingRestock,
                        'restock_note'        => $restockNote,
                    ]);

                    foreach ($cart->items as $item) {

                        $price = $item->variant
                            ? $item->variant->price
                            : $item->product->price;

                        OrderItem::create([
                            'order_id'                 => $order->id,
                            'product_id'               => $item->product_id,
                            'variant_id'               => $item->variant_id,
                            'quantity'                 => $item->quantity,
                            'price'                    => $price,
                            'subtotal'                 => $price * $item->quantity,
                            'is_waiting_restock'       => $item->is_waiting_restock,
                            'waiting_restock_quantity' => $item->waiting_restock_quantity ?? 0,
                        ]);
                    }

                    $cart->items()->delete();

                });

            } catch (\Exception $e) {

                return redirect()
                    ->route('cart.index')
                    ->with('error', $e->getMessage());
            }

            return redirect()
                ->route('orders.index')
                ->with('success', 'Pesanan COD berhasil dibuat.');

        } catch (\Exception $e) {
            // ✅ FIX #2: Tampilkan pesan error yang spesifik ke user
            return redirect()
                ->route('cart.index')
                ->with('error', $e->getMessage() ?: 'Terjadi kesalahan saat checkout. Silakan coba lagi.');
        }

    }
    /**
     * Halaman pembayaran QRIS.
     */

    public function tempPayment()
    {
        if (!session()->has('checkout_data')) {
            return redirect()->route('checkout.index');
        }

        return view('checkout.payment-temp');
    }

    public function finalize(Request $request)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (!session()->has('checkout_data')) {
            return redirect()->route('checkout.index');
        }

        $checkout = session('checkout_data');

        $cart = Cart::where('user_id', auth()->id())
            ->with(['items.product', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang kosong.');
        }

        $subtotal = 0;

        foreach ($cart->items as $item) {

            $price = $item->variant
                ? $item->variant->price
                : $item->product->price;

            $subtotal += $price * $item->quantity;
        }

        $shippingCost = $checkout['delivery_method'] === 'ojek_toko'
            ? 10000
            : 0;

        $grandTotal = $subtotal + $shippingCost;

        $proofPath = $request->file('payment_proof')
            ->store('payment_proofs', 'public');

        try {

            DB::transaction(function () use (
                $cart,
                $checkout,
                $subtotal,
                $shippingCost,
                $grandTotal,
                $proofPath
            ) {

                foreach ($cart->items as $item) {

                    $product = Product::lockForUpdate()
                        ->find($item->product_id);

                    if (!$product) {
                        throw new \Exception('Produk tidak ditemukan.');
                    }

                    if (
                        !$item->is_waiting_restock &&
                        $item->quantity > $product->stock_quantity
                    ) {
                        throw new \Exception(
                            $this->stockErrorMessage($product)
                        );
                    }

                    $stockToReduce = min(
                        $item->quantity,
                        $product->stock_quantity
                    );

                    if ($stockToReduce > 0) {
                        $product->decrement(
                            'stock_quantity',
                            $stockToReduce
                        );
                    }
                }

                $order = Order::create([
                    'order_code'        => $this->generateShortOrderCode(),
                    'user_id'           => auth()->id(),
                    'subtotal'          => $subtotal,
                    'shipping_cost'     => $shippingCost,
                    'discount_amount'   => 0,
                    'grand_total'       => $grandTotal,
                    'payment_method'    => 'qris',
                    'payment_status'    => 'pending',
                    'order_status'      => 'waiting_confirmation',
                    'shipping_address'  => $checkout['shipping_address'],
                    'customer_whatsapp' => $checkout['customer_whatsapp'],
                    'house_landmark'    => $checkout['house_landmark'],
                    'delivery_method'   => $checkout['delivery_method'],
                    'notes'             => $checkout['notes'],
                    'payment_proof'     => $proofPath,
                ]);

                foreach ($cart->items as $item) {

                    $price = $item->variant
                        ? $item->variant->price
                        : $item->product->price;

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'quantity'   => $item->quantity,
                        'price'      => $price,
                        'subtotal'   => $price * $item->quantity,
                    ]);
                }

                $cart->items()->delete();
            });

        } catch (\Exception $e) {

            return redirect()
                ->route('cart.index')
                ->with('error', $e->getMessage());
        }

        session()->forget('checkout_data');

        return redirect()
            ->route('orders.index')
            ->with('success', 'Pesanan berhasil dibuat dan menunggu konfirmasi admin.');
    }
}