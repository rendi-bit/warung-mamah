<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    private function initMidtransConfig(): void
    {
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('midtrans.is_production');
        \Midtrans\Config::$isSanitized  = (bool) config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds        = (bool) config('midtrans.is_3ds');
    }

    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())->with(['items.product', 'items.variant'])->first();

        if (!$cart || $cart->items->count() == 0) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang kosong.');
        }

        return view('checkout.index', compact('cart'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|max:1000',
        ]);

        $cart = Cart::where('user_id', auth()->id())
            ->with(['items.product', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->count() == 0) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang kosong.');
        }

        $subtotal = 0;

        foreach ($cart->items as $item) {
            $price = $item->variant ? $item->variant->price : $item->product->price;
            $subtotal += ($price * $item->quantity);
        }

        $orderCode = 'ORD-' . strtoupper(Str::random(10));

        $order = Order::create([
            'order_code'        => $orderCode,
            'user_id'           => auth()->id(),
            'subtotal'          => $subtotal,
            'shipping_cost'     => 0,
            'discount_amount'   => 0,
            'grand_total'       => $subtotal,
            'payment_method'    => 'midtrans',
            'payment_status'    => 'pending',
            'order_status'      => 'pending',
            'shipping_address'  => $request->shipping_address,
        ]);

        foreach ($cart->items as $item) {
             $price = $item->variant ? $item->variant->price : $item->product->price;

             OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'quantity'   => $item->quantity,
                'price' => $price,
                'subtotal' => $price * $item->quantity,
            ]);
        }

        $cart->items()->delete();

        return redirect()->route('checkout.payment', $order->id);
    }

    public function payment(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $this->initMidtransConfig();

        $params = [
            'transaction_details' => [
                'order_id'     => $order->order_code,
                'gross_amount' => (int) $order->grand_total,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email'      => auth()->user()->email,
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return view('checkout.payment', compact('order', 'snapToken'));
    }
}