<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\WishlistItem;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlist = Wishlist::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        $items = $wishlist->items()
            ->with(['product.category', 'product.variants'])
            ->latest()
            ->get();

        return view('wishlist.index', compact('items'));
    }

    public function add($productId)
    {
        $wishlist = Wishlist::firstOrCreate([
            'user_id' => auth()->id(),
        ]);

        WishlistItem::firstOrCreate([
            'wishlist_id' => $wishlist->id,
            'product_id' => $productId,
        ]);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke favorit.');
    }

    public function remove($id)
    {
        $wishlist = Wishlist::where('user_id', auth()->id())->firstOrFail();

        $item = WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('id', $id)
            ->firstOrFail();

        $item->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus dari favorit.');
    }
}