<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Pesan error stok yang informatif.
     */
    private function stockErrorMessage(Product $product): string
    {
        return 'Maaf, stok ' . $product->name . ' saat ini belum mencukupi. ' .
            'Stok tersedia hanya ' . $product->stock_quantity . ' ' . ($product->stock_unit ?? 'pcs') . '. ' .
            'Diperkirakan restok dalam ' . ($product->restock_estimation ?? '1-2 hari') . '. ' .
            'Silakan kurangi jumlah atau pilih "Tunggu Restok".';
    }

    /**
     * Validasi variant milik produk yang dimaksud.
     */
    private function validateVariant(Product $product, $variantId): void
    {
        if ($variantId) {
            ProductVariant::where('product_id', $product->id)
                ->where('id', $variantId)
                ->firstOrFail();
        }
    }

    /**
     * Hitung status restock berdasarkan stok FRESH dari DB.
     * ✅ FIX: Gunakan fresh() agar stok selalu akurat, tidak dari cache objek.
     */
    private function calculateRestockStatus(Product $product, int $totalRequestedQty, bool $allowWaitingRestock): array
    {
        // Ambil stok terbaru langsung dari DB
        $freshStock = $product->fresh()->stock_quantity;

        if ($totalRequestedQty <= $freshStock) {
            return [
                'is_waiting_restock'       => false,
                'waiting_restock_quantity' => 0,
            ];
        }

        if (!$allowWaitingRestock) {
            return [
                'error'   => true,
                'message' => $this->stockErrorMessage($product),
            ];
        }

        return [
            'is_waiting_restock'       => true,
            'waiting_restock_quantity' => $totalRequestedQty - $freshStock,
        ];
    }

    /**
     * Validasi rules untuk add/buyNow.
     */
    private function cartValidationRules(Product $product): array
    {
        $rules = [
            'quantity'              => 'required|integer|min:1|max:999',
            'allow_waiting_restock' => 'nullable|boolean',
        ];

        $rules['variant_id'] = $product->variants->count()
            ? 'required|exists:product_variants,id'
            : 'nullable|exists:product_variants,id';

        return $rules;
    }

    /**
     * ✅ FIX: Logika add ke keranjang di-extract ke satu method.
     * Dipakai oleh add() dan buyNow() agar tidak duplikat.
     */
    private function addToCart(Product $product, int $qty, $variantId, bool $allowWaitingRestock): CartItem
    {
        $this->validateVariant($product, $variantId);

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variantId)
            ->first();

        $currentQty        = $item ? $item->quantity : 0;
        $totalRequestedQty = $currentQty + $qty;

        $restockStatus = $this->calculateRestockStatus($product, $totalRequestedQty, $allowWaitingRestock);

        if (isset($restockStatus['error'])) {
            throw new \Exception($restockStatus['message']);
        }

        if ($item) {
            $item->update([
                'quantity'                 => $totalRequestedQty,
                'is_waiting_restock'       => $restockStatus['is_waiting_restock'],
                'waiting_restock_quantity' => $restockStatus['waiting_restock_quantity'],
            ]);
        } else {
            $item = CartItem::create([
                'cart_id'                  => $cart->id,
                'product_id'               => $product->id,
                'variant_id'               => $variantId,
                'quantity'                 => $qty,
                'is_waiting_restock'       => $restockStatus['is_waiting_restock'],
                'waiting_restock_quantity' => $restockStatus['waiting_restock_quantity'],
            ]);
        }

        return $item;
    }

    /**
     * Tampilkan halaman keranjang.
     */
    public function index()
    {
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

        $items = $cart->items()->with(['product', 'variant'])->get();

        return view('cart.index', compact('items'));
    }

    /**
     * Tambah produk ke keranjang.
     */
    public function add(Request $request, $productId)
    {
        $product = Product::with('variants')->findOrFail($productId);

        $request->validate($this->cartValidationRules($product));

        try {
            $this->addToCart(
                $product,
                (int) $request->quantity,
                $request->variant_id,
                $request->boolean('allow_waiting_restock')
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Update jumlah item di keranjang.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity'              => 'required|integer|min:1|max:999',
            'allow_waiting_restock' => 'nullable|boolean',
        ]);

        $item = CartItem::with('product')->findOrFail($id);

        // ✅ Pastikan item milik cart user yang login
        $cart = Cart::where('user_id', auth()->id())->firstOrFail();
        if ($item->cart_id !== $cart->id) {
            abort(403, 'Akses ditolak.');
        }

        $restockStatus = $this->calculateRestockStatus(
            $item->product,
            (int) $request->quantity,
            $request->boolean('allow_waiting_restock')
        );

        if (isset($restockStatus['error'])) {
            return back()->with('error', $restockStatus['message']);
        }

        $item->update([
            'quantity'                 => (int) $request->quantity,
            'is_waiting_restock'       => $restockStatus['is_waiting_restock'],
            'waiting_restock_quantity' => $restockStatus['waiting_restock_quantity'],
        ]);

        return back()->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    /**
     * Hapus item dari keranjang.
     */
    public function remove($id)
    {
        $item = CartItem::findOrFail($id);

        // ✅ Pastikan item milik cart user yang login
        $cart = Cart::where('user_id', auth()->id())->firstOrFail();
        if ($item->cart_id !== $cart->id) {
            abort(403, 'Akses ditolak.');
        }

        $item->delete();

        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    /**
     * Beli langsung — tambah ke keranjang lalu redirect ke checkout.
     * ✅ FIX: Menggunakan addToCart() yang sama dengan add(), tidak duplikat.
     */
    public function buyNow(Request $request, $productId)
    {
        $product = Product::with('variants')->findOrFail($productId);

        $request->validate($this->cartValidationRules($product));

        try {
            $this->addToCart(
                $product,
                (int) $request->quantity,
                $request->variant_id,
                $request->boolean('allow_waiting_restock')
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('checkout.index')
            ->with('success', 'Produk siap dibeli. Silakan lanjut checkout.');
    }
}