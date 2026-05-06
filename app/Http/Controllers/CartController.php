<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
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
        ];

        if ($product->variants->count()) {
            $rules['variant_id'] = 'required|exists:product_variants,id';
        } else {
            $rules['variant_id'] = 'nullable|exists:product_variants,id';
        }

        $request->validate($rules);

        $qty = (int) $request->quantity;
        $variantId = $request->variant_id;

        if ($variantId) {
            ProductVariant::where('product_id', $product->id)
                ->where('id', $variantId)
                ->firstOrFail();
        }

        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id()
        ]);

        $currentProductQtyInCart = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->sum('quantity');

        if (($currentProductQtyInCart + $qty) > $product->stock_quantity) {
            return back()->with('error', 'Jumlah total melebihi stok produk yang tersedia.');
        }

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($item) {
            $item->update([
                'quantity' => $item->quantity + $qty
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $qty,
            ]);
        }

        return back()->with('success', 'Produk berhasil masuk ke keranjang belanja.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = CartItem::with('product')->findOrFail($id);

        if ($request->quantity > $item->product->stock_quantity) {
            return back()->with('error', 'Jumlah melebihi stok produk yang tersedia.');
        }

        $item->update([
            'quantity' => $request->quantity
        ]);

        return back()->with('success', 'Jumlah produk di keranjang berhasil diperbarui.');
    }

   public function remove($id)
    {
        $item = CartItem::findOrFail($id);
        $item->delete();

        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }
    
    public function buyNow(Request $request, $productId)
    {
        $product = Product::with('variants')->findOrFail($productId);

        $rules = [
            'quantity' => 'required|integer|min:1',
        ];

        if ($product->variants->count()) {
            $rules['variant_id'] = 'required|exists:product_variants,id';
        } else {
            $rules['variant_id'] = 'nullable|exists:product_variants,id';
        }

        $request->validate($rules);

        $qty = (int) $request->quantity;
        $variantId = $request->variant_id;

        if ($variantId) {
            ProductVariant::where('product_id', $product->id)
                ->where('id', $variantId)
                ->firstOrFail();
        }

        if ($qty > $product->stock_quantity) {
            return back()->with('error', 'Jumlah melebihi stok produk yang tersedia.');
        }

        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($item) {
            $newQty = $item->quantity + $qty;

            if ($newQty > $product->stock_quantity) {
                return back()->with('error', 'Jumlah total di keranjang melebihi stok produk.');
            }

            $item->update([
                'quantity' => $newQty,
            ]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $qty,
            ]);
        }

        return redirect()
            ->route('checkout.index')
            ->with('success', 'Produk siap dibeli. Silakan lanjut checkout.');
    }
}