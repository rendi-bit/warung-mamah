<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    private function generateShortOrderCode(): string
    {
        do {
            $code = strtoupper(Str::random(5));
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }

    private function stockErrorMessage($product): string
    {
        return 'Maaf, stok ' . $product->name . ' saat ini belum mencukupi. ' .
            'Stok tersedia hanya ' . $product->stock_quantity . ' ' . $product->stock_unit . '. ' .
            'Produk sedang dalam proses restok dan diperkirakan tersedia dalam ' . ($product->restock_estimation ?? '1 hari') . '.';
    }

    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())
            ->with(['items.product', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->count() == 0) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang kosong.');
        }

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (!$product) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', 'Ada produk yang tidak ditemukan di keranjang.');
            }

            if ($item->quantity > $product->stock_quantity && !$item->is_waiting_restock) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', $this->stockErrorMessage($product));
            }
        }

        return view('checkout.index', compact('cart'));
    }

    public function process(Request $request)
{
    $request->validate([
        'shipping_address'   => 'required|string|max:1000',
        'customer_whatsapp'  => 'required|string|max:30',
        'house_landmark'     => 'nullable|string|max:255',
        'notes'              => 'nullable|string|max:1000',
        'delivery_method'    => 'required|in:ojek_toko,ambil_di_toko',
        'payment_method'     => 'required|in:qris,cod',
    ]);

    $cart = Cart::where('user_id', auth()->id())
        ->with(['items.product', 'items.variant'])
        ->first();

    if (!$cart || $cart->items->count() == 0) {
        return redirect()
            ->route('cart.index')
            ->with('error', 'Keranjang kosong.');
    }

    foreach ($cart->items as $item) {
        $product = $item->product;

        if (!$product) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Ada produk yang tidak ditemukan di keranjang.');
        }

        if ($item->quantity > $product->stock_quantity && !$item->is_waiting_restock) {
            return redirect()
                ->route('cart.index')
                ->with('error', $this->stockErrorMessage($product));
        }
    }

    $subtotal = 0;

    foreach ($cart->items as $item) {
        $price = $item->variant ? $item->variant->price : $item->product->price;
        $subtotal += ($price * $item->quantity);
    }

    $shippingCost = $request->delivery_method === 'ojek_toko' ? 10000 : 0;
    $grandTotal = $subtotal + $shippingCost;

    $orderCode = $this->generateShortOrderCode();

    $hasWaitingRestock = $cart->items
        ->where('is_waiting_restock', true)
        ->count() > 0;

    $restockNote = $hasWaitingRestock
        ? 'Pesanan memiliki item yang menunggu restok.'
        : null;

    try {

        $order = DB::transaction(function () use (
            $request,
            $cart,
            $subtotal,
            $shippingCost,
            $grandTotal,
            $orderCode,
            $hasWaitingRestock,
            $restockNote
        ) {

            $order = Order::create([
                'order_code'          => $orderCode,
                'user_id'             => auth()->id(),
                'subtotal'            => $subtotal,
                'shipping_cost'       => $shippingCost,
                'discount_amount'     => 0,
                'grand_total'         => $grandTotal,
                'payment_method'      => $request->payment_method,
                'payment_status'      => 'pending',
                'order_status'        => 'pending',
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
                    'waiting_restock_quantity' => $item->waiting_restock_quantity,
                ]);

                $stockToReduce = min(
                    $item->quantity,
                    $item->product->stock_quantity
                );

                if ($stockToReduce > 0) {
                    $item->product->decrement(
                        'stock_quantity',
                        $stockToReduce
                    );
                }
            }

            $cart->items()->delete();

            return $order;
        });

    } catch (\Exception $e) {

        return redirect()
            ->route('cart.index')
            ->with('error', 'Terjadi kesalahan saat checkout.');
    }

    if ($request->payment_method === 'cod') {

        return redirect()
            ->route('orders.index')
            ->with(
                'success',
                'Pesanan berhasil dibuat. Pembayaran dilakukan secara COD.'
            );
    }

    return redirect()
        ->route('checkout.payment', $order->id)
        ->with(
            'success',
            'Pesanan berhasil dibuat. Silakan lakukan pembayaran QRIS.'
        );
}

    public function payment(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_method === 'cod') {
            return redirect()
                ->route('orders.index')
                ->with('info', 'Pesanan ini menggunakan pembayaran COD.');
        }

        return view('checkout.payment', compact('order'));
    }
}