<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function stockErrorMessage(Product $product): string
    {
        return 'Maaf, stok ' . $product->name . ' saat ini belum mencukupi. ' .
            'Stok tersedia hanya ' . $product->stock_quantity . ' ' . $product->stock_unit . '. ' .
            'Produk sedang dalam proses restok dan diperkirakan tersedia dalam ' . ($product->restock_estimation ?? '1 hari') . '. ' .
            'Silakan kurangi jumlah pembelian sesuai stok tersedia atau pilih tetap pesan dan tunggu restok.';
    }

    private function validateVariant(Product $product, $variantId): void
    {
        if ($variantId) {
            ProductVariant::where('product_id', $product->id)
                ->where('id', $variantId)
                ->firstOrFail();
        }
    }

    private function calculateRestockStatus(Product $product, int $totalRequestedQty, bool $allowWaitingRestock): array
    {
        if ($totalRequestedQty <= $product->stock_quantity) {
            return [
                'is_waiting_restock' => false,
                'waiting_restock_quantity' => 0,
            ];
        }

        if (!$allowWaitingRestock) {
            return [
                'error' => true,
                'message' => $this->stockErrorMessage($product),
            ];
        }

        return [
            'is_waiting_restock' => true,
            'waiting_restock_quantity' => $totalRequestedQty - $product->stock_quantity,
        ];
    }

    public function index()
    {
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $items = $cart->items()->with(['product', 'variant'])->get();

        return view('cart.index', compact('items'));
    }

    public function add(Request $request, $productId)
    {
        $product = Product::with('variants')->findOrFail($productId);

        $rules = [
            'quantity' => 'required|integer|min:1',
            'allow_waiting_restock' => 'nullable|boolean',
        ];

        if ($product->variants->count()) {
            $rules['variant_id'] = 'required|exists:product_variants,id';
        } else {
            $rules['variant_id'] = 'nullable|exists:product_variants,id';
        }

        $request->validate($rules);

        $qty = (int) $request->quantity;
        $variantId = $request->variant_id;
        $allowWaitingRestock = $request->boolean('allow_waiting_restock');

        $this->validateVariant($product, $variantId);

        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        $currentQty = $item ? $item->quantity : 0;
        $totalRequestedQty = $currentQty + $qty;

        $restockStatus = $this->calculateRestockStatus($product, $totalRequestedQty, $allowWaitingRestock);

        if (isset($restockStatus['error'])) {
            return back()->with('error', $restockStatus['message']);
        }

        if ($item) {
            $item->update([
                'quantity' => $totalRequestedQty,
                'is_waiting_restock' => $restockStatus['is_waiting_restock'],
                'waiting_restock_quantity' => $restockStatus['waiting_restock_quantity'],
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $qty,
                'is_waiting_restock' => $restockStatus['is_waiting_restock'],
                'waiting_restock_quantity' => $restockStatus['waiting_restock_quantity'],
            ]);
        }

        return back()->with('success', 'Produk berhasil masuk ke keranjang belanja.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'allow_waiting_restock' => 'nullable|boolean',
        ]);

        $item = CartItem::with('product')->findOrFail($id);

        $cart = Cart::where('user_id', auth()->id())->firstOrFail();
        if ($item->cart_id !== $cart->id) {
            abort(403);
        }
        $product = $item->product;
        $qty = (int) $request->quantity;
        $allowWaitingRestock = $request->boolean('allow_waiting_restock');

        $restockStatus = $this->calculateRestockStatus($product, $qty, $allowWaitingRestock);

        if (isset($restockStatus['error'])) {
            return back()->with('error', $restockStatus['message']);
        }

        $item->update([
            'quantity' => $qty,
            'is_waiting_restock' => $restockStatus['is_waiting_restock'],
            'waiting_restock_quantity' => $restockStatus['waiting_restock_quantity'],
        ]);

        return back()->with('success', 'Jumlah produk di keranjang berhasil diperbarui.');
    }

    public function remove($id)
    {
        $item = CartItem::findOrFail($id);

        $cart = Cart::where('user_id', auth()->id())->firstOrFail();
        if ($item->cart_id !== $cart->id) {
            abort(403);
        }

        $item->delete();

        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    public function buyNow(Request $request, $productId)
    {
        $product = Product::with('variants')->findOrFail($productId);

        $rules = [
            'quantity' => 'required|integer|min:1',
            'allow_waiting_restock' => 'nullable|boolean',
        ];

        if ($product->variants->count()) {
            $rules['variant_id'] = 'required|exists:product_variants,id';
        } else {
            $rules['variant_id'] = 'nullable|exists:product_variants,id';
        }

        $request->validate($rules);

        $qty = (int) $request->quantity;
        $variantId = $request->variant_id;
        $allowWaitingRestock = $request->boolean('allow_waiting_restock');

        $this->validateVariant($product, $variantId);

        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        $currentQty = $item ? $item->quantity : 0;
        $totalRequestedQty = $currentQty + $qty;

        $restockStatus = $this->calculateRestockStatus($product, $totalRequestedQty, $allowWaitingRestock);

        if (isset($restockStatus['error'])) {
            return back()->with('error', $restockStatus['message']);
        }

        if ($item) {
            $item->update([
                'quantity' => $totalRequestedQty,
                'is_waiting_restock' => $restockStatus['is_waiting_restock'],
                'waiting_restock_quantity' => $restockStatus['waiting_restock_quantity'],
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $qty,
                'is_waiting_restock' => $restockStatus['is_waiting_restock'],
                'waiting_restock_quantity' => $restockStatus['waiting_restock_quantity'],
            ]);
        }

        return redirect()
            ->route('checkout.index')
            ->with('success', 'Produk siap dibeli. Silakan lanjut checkout.');
    }
}