<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
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

    $request->validate([
        'quantity' => 'required|integer|min:1',
        'variant_id' => 'nullable|exists:product_variants,id',
    ]);

    $qty = (int) $request->quantity;
    $variantId = $request->variant_id;

    $variant = null;
    $availableStock = $product->stock_quantity;

    if ($variantId) {
        $variant = \App\Models\ProductVariant::findOrFail($variantId);
        $availableStock = $variant->stock;
    }

    if ($qty > $availableStock) {
        return redirect()->back()->with('error', 'Jumlah melebihi stok yang tersedia');
    }

    $cart = Cart::firstOrCreate([
        'user_id' => auth()->id()
    ]);

    $item = CartItem::where('cart_id', $cart->id)
        ->where('product_id', $productId)
        ->where('variant_id', $variantId)
        ->first();

    if ($item) {
        $newQty = $item->quantity + $qty;

        if ($newQty > $availableStock) {
            return redirect()->back()->with('error', 'Jumlah total di keranjang melebihi stok');
        }

        $item->update([
            'quantity' => $newQty
        ]);
    } else {
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $qty,
        ]);
    }

    return redirect()->back()->with('success', 'Produk ditambahkan ke keranjang');
}

    public function update(Request $request, $id)
    {
        $item = CartItem::findOrFail($id);
        $item->update([
            'quantity' => $request->quantity
        ]);

        return redirect()->back()->with('success', 'Keranjang diupdate');
    }

    public function remove($id)
    {
        $item = CartItem::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Item dihapus');
    }
}                           