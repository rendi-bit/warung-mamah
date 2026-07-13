<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\ShippingArea;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    private function generateShortOrderCode(): string
    {
        $maxAttempts = 10;
        $attempt     = 0;

        do {
            $code = strtoupper(Str::random(5));
            $attempt++;

            if ($attempt >= $maxAttempts) {
                $code = strtoupper(Str::random(3)) . substr(time(), -2);
                break;
            }
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }

    private function stockErrorMessage($product): string
    {
        return 'Maaf, stok ' . $product->name . ' saat ini tidak mencukupi. ' .
            'Stok tersedia: ' . $product->stock_quantity . ' ' . ($product->stock_unit ?? 'pcs') . '. ' .
            'Diperkirakan restok dalam ' . ($product->restock_estimation ?? '1-2 hari') . '.';
    }

    /**
     * ✅ FIX: Pesan error khusus untuk stok VARIAN yang tidak mencukupi.
     */
    private function variantStockErrorMessage($product, $variant): string
    {
        return 'Maaf, stok ' . $product->name . ' (' . $variant->variant_name . ') saat ini tidak mencukupi. ' .
            'Stok tersedia: ' . $variant->stock . '. ' .
            'Diperkirakan restok dalam ' . ($product->restock_estimation ?? '1-2 hari') . '.';
    }

    /**
     * ✅ Helper: Hitung kebutuhan stok (dalam satuan stock_quantity) untuk sebuah item.
     * HANYA dipakai untuk produk TANPA varian (produk berbasis stock_quantity/berat).
     * - Jika item punya varian dengan berat (gram) → dikonversi ke kg, dikali quantity.
     * - Jika item tanpa varian → kebutuhan stok = quantity apa adanya.
     */
    private function requiredStock($item): float
    {
        if ($item->variant && $item->variant->weight) {
            $weightInKg = $item->variant->weight / 1000;
            return $weightInKg * $item->quantity;
        }

        return (float) $item->quantity;
    }

    /**
     * ✅ FIX: Validasi stok untuk 1 item cart/order.
     * - Item punya variant_id → cek stok di ProductVariant (bukan stock_quantity produk utama).
     * - Item tanpa variant_id → cek stok di Product::stock_quantity (perilaku lama, tidak berubah).
     * Melempar \Exception kalau stok tidak cukup dan item tidak sedang waiting_restock.
     */
    private function assertStockAvailable($product, $item): void
    {
        if ($item->variant_id) {
            $variant = $item->variant ?: ProductVariant::find($item->variant_id);

            if (!$variant) {
                throw new \Exception('Varian produk tidak ditemukan.');
            }

            if (!$item->is_waiting_restock && $item->quantity > $variant->stock) {
                throw new \Exception($this->variantStockErrorMessage($product, $variant));
            }

            return;
        }

        $requiredStock = $this->requiredStock($item);

        if (!$item->is_waiting_restock && $requiredStock > $product->stock_quantity) {
            throw new \Exception($this->stockErrorMessage($product));
        }
    }

    /**
     * ✅ Helper: Kurangi stok produk
     * - is_waiting_restock = true  → stok boleh minus (kurangi penuh)
     * - is_waiting_restock = false → stok dikurangi normal (TIDAK BOLEH minus)
     */
    private function reduceStock(Product $product, $item): void
    {
        // Jika produk memakai varian, kurangi stok varian
        if ($item->variant_id) {

            $variant = ProductVariant::where('id', $item->variant_id)
                ->lockForUpdate()
                ->first();

            if (!$variant) {
                return;
            }

            if ($item->is_waiting_restock) {
                // Boleh minus, kurangi penuh sesuai quantity
                $variant->decrement('stock', $item->quantity);
            } else {
                // ✅ FIX: Jangan sampai minus. Kurangi maksimal sebanyak stok yang tersedia.
                $qtyToReduce = min($item->quantity, $variant->stock);

                if ($qtyToReduce > 0) {
                    $variant->decrement('stock', $qtyToReduce);
                }
            }

            return;
        }

        // Produk tanpa varian (kode lama tetap)
        $stockToReduce = $this->requiredStock($item);

        if ($item->is_waiting_restock) {

            $product->decrement('stock_quantity', $stockToReduce);

        } else {

            $stockToReduce = min($stockToReduce, $product->stock_quantity);

            if ($stockToReduce > 0) {
                $product->decrement('stock_quantity', $stockToReduce);
            }
        }
    }

    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())
            ->with(['items.product', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja kosong.');
        }

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (!$product) {
                return redirect()->route('cart.index')
                    ->with('error', 'Ada produk yang tidak ditemukan. Silakan perbarui keranjang Anda.');
            }

            // ✅ FIX: Validasi stok varian ATAU stok produk, tergantung jenis item
            try {
                $this->assertStockAvailable($product, $item);
            } catch (\Exception $e) {
                return redirect()->route('cart.index')
                    ->with('error', $e->getMessage());
            }
        }

        $lastOrder = Order::where('user_id', Auth::id())->latest()->first();

        $shippingAreas = ShippingArea::orderBy('kelurahan')->get();

        return view('checkout.index', compact(
            'cart',
            'lastOrder',
            'shippingAreas'
        ));
    }

    public function process(Request $request)
    {
        $request->validate([
            'shipping_address'  => 'required|string|max:1000',
            'customer_whatsapp' => 'required|string|max:30',
            'shipping_area'     => 'required|exists:shipping_areas,id',
            'house_landmark'    => 'nullable|string|max:255',
            'notes'             => 'nullable|string|max:1000',
            'delivery_method'   => 'required|in:ojek_toko,ambil_di_toko',
            'payment_method'    => 'required|in:qris,cod',
        ]);

        $cart = Cart::where('user_id', Auth::id())
            ->with(['items.product', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang belanja kosong.');
        }

        $subtotal = 0;
        foreach ($cart->items as $item) {
            $price    = $item->variant ? $item->variant->price : $item->product->price;
            $subtotal += $price * $item->quantity;
        }

        $shippingCost = 0;

        if ($request->delivery_method === 'ojek_toko') {

            $shippingArea = ShippingArea::findOrFail(
                $request->shipping_area
            );

            $shippingCost = $shippingArea->shipping_cost;
        }
        $grandTotal        = $subtotal + $shippingCost;
        $orderCode         = $this->generateShortOrderCode();
        $hasWaitingRestock = $cart->items->contains('is_waiting_restock', true);
        $restockNote       = $hasWaitingRestock ? 'Pesanan memiliki item yang menunggu restok.' : null;

        try {
            // QRIS — simpan ke session lalu ke halaman payment-temp
            if ($request->payment_method === 'qris') {
                session(['checkout_data' => [
                    'shipping_address'  => $request->shipping_address,
                    'customer_whatsapp' => $request->customer_whatsapp,
                    'shipping_area'     => $request->shipping_area,
                    'shipping_cost'     => $shippingCost,
                    'house_landmark'    => $request->house_landmark,
                    'notes'             => $request->notes,
                    'delivery_method'   => $request->delivery_method,
                    'payment_method'    => $request->payment_method,
                ]]);

                return redirect()->route('checkout.payment.temp');
            }

            // COD
            try {
                DB::transaction(function () use (
                    $request, $cart, $subtotal, $shippingCost,
                    $grandTotal, $orderCode, $hasWaitingRestock, $restockNote
                ) {
                    foreach ($cart->items as $item) {
                        $product = Product::lockForUpdate()->find($item->product_id);

                        if (!$product) {
                            throw new \Exception('Produk tidak ditemukan.');
                        }

                        // ✅ FIX: Validasi stok varian ATAU stok produk, tergantung jenis item
                        $this->assertStockAvailable($product, $item);

                        $this->reduceStock($product, $item);
                    }

                    $order = Order::create([
                        'order_code'          => $orderCode,
                        'user_id'             => Auth::id(),
                        'shipping_area_id' => $request->shipping_area,
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
                        $price = $item->variant ? $item->variant->price : $item->product->price;

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
                return redirect()->route('cart.index')
                    ->with('error', $e->getMessage());
            }

            return redirect()->route('orders.index')
                ->with('success', 'Pesanan COD berhasil dibuat.');

        } catch (\Exception $e) {
            return redirect()->route('cart.index')
                ->with('error', $e->getMessage() ?: 'Terjadi kesalahan saat checkout. Silakan coba lagi.');
        }
    }

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

        $cart = Cart::where('user_id', Auth::id())
            ->with(['items.product', 'items.variant'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Keranjang kosong.');
        }

        $subtotal = 0;
        foreach ($cart->items as $item) {
            $price    = $item->variant ? $item->variant->price : $item->product->price;
            $subtotal += $price * $item->quantity;
        }

        $shippingCost = $checkout['shipping_cost'];
        $grandTotal   = $subtotal + $shippingCost;
        $proofPath    = $request->file('payment_proof')->store('payment_proofs', 'public');

        try {
            DB::transaction(function () use (
                $cart, $checkout, $subtotal, $shippingCost, $grandTotal, $proofPath
            ) {
                foreach ($cart->items as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);

                    if (!$product) {
                        throw new \Exception('Produk tidak ditemukan.');
                    }

                    // ✅ FIX: Validasi stok varian ATAU stok produk, tergantung jenis item
                    $this->assertStockAvailable($product, $item);

                    $this->reduceStock($product, $item);
                }

                $order = Order::create([
                    'order_code'        => $this->generateShortOrderCode(),
                    'user_id'           => Auth::id(),
                    'shipping_area_id' => $checkout['shipping_area'],
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
                    $price = $item->variant ? $item->variant->price : $item->product->price;

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'quantity'   => $item->quantity,
                        'price'      => $price,
                        'subtotal'   => $price * $item->quantity,
                        'is_waiting_restock'       => $item->is_waiting_restock,
                        'waiting_restock_quantity' => $item->waiting_restock_quantity ?? 0,
                    ]);
                }

                $cart->items()->delete();
            });

        } catch (\Exception $e) {
            return redirect()->route('cart.index')
                ->with('error', $e->getMessage());
        }

        session()->forget('checkout_data');

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan berhasil dibuat dan menunggu konfirmasi admin.');
    }

    public function payment(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        return redirect()->route('orders.invoice', $order->id);
    }
}