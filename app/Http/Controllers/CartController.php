<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Pesan error stok yang informatif — untuk produk TANPA varian.
     */
    private function stockErrorMessage(Product $product): string
    {
        return 'Maaf, stok ' . $product->name . ' saat ini belum mencukupi. ' .
            'Stok tersedia hanya ' . $product->stock_quantity . ' ' . ($product->stock_unit ?? 'kg') . '. ' .
            'Diperkirakan restok dalam ' . ($product->restock_estimation ?? '1-2 hari') . '. ' .
            'Silakan kurangi jumlah atau pilih "Tunggu Restok".';
    }

    /**
     * ✅ FIX: Pesan error stok khusus untuk produk DENGAN varian.
     * Menampilkan total sisa stok induk produk asli agar tetap sinkron.
     */
    private function variantStockErrorMessage(Product $product, ProductVariant $variant): string
    {
        $freshProduct = $product->fresh();
        return 'Maaf, stok ' . $product->name . ' (' . $variant->variant_name . ') saat ini belum mencukupi. ' .
            'Stok tersedia hanya ' . $freshProduct->stock_quantity . ' ' . ($freshProduct->stock_unit ?? 'kg') . '. ' .
            'Diperkirakan restok dalam ' . ($product->restock_estimation ?? '1-2 hari') . '. ' .
            'Silakan kurangi jumlah atau pilih "Tunggu Restok".';
    }

    /**
     * Validasi variant milik produk yang dimaksud, dan kembalikan modelnya.
     */
    private function validateVariant(Product $product, $variantId): ?ProductVariant
    {
        if ($variantId) {
            return ProductVariant::where('product_id', $product->id)
                ->where('id', $variantId)
                ->firstOrFail();
        }

        return null;
    }

    /**
     * ✅ FIX FINAL: Hitung status restock berdasarkan STOK UTAMA produk induk.
     * Baik produk memiliki varian maupun tidak, semuanya mengacu pada kantong stok yang sama: products.stock_quantity.
     */
    private function calculateRestockStatus(
        Product $product,
        int $totalRequestedQty,
        bool $allowWaitingRestock,
        ?ProductVariant $variant = null
    ): array {
        // Ambil data stok ter-fresh dari database milik produk utama
        $freshStock = $product->fresh()->stock_quantity;

        // Cek apakah stok utama mencukupi untuk jumlah yang diminta
        if ($totalRequestedQty <= $freshStock) {
            return [
                'is_waiting_restock'       => false,
                'waiting_restock_quantity' => 0,
            ];
        }

        // Jika stok tidak cukup dan user TIDAK memilih opsi "Tunggu Restok"
        if (!$allowWaitingRestock) {
            return [
                'error'   => true,
                'message' => $variant 
                    ? $this->variantStockErrorMessage($product, $variant) 
                    : $this->stockErrorMessage($product),
            ];
        }

        // Jika stok tidak cukup tapi user bersedia menunggu restok
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
     * Logika add ke keranjang.
     */
    private function addToCart(Product $product, int $qty, $variantId, bool $allowWaitingRestock): CartItem
    {
        $variant = $this->validateVariant($product, $variantId);

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variantId)
            ->first();

        $currentQty        = $item ? $item->quantity : 0;
        $totalRequestedQty = $currentQty + $qty;

        $restockStatus = $this->calculateRestockStatus($product, $totalRequestedQty, $allowWaitingRestock, $variant);

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

    public function index()
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $items = $cart->items()->with(['product', 'variant'])->get();
        return view('cart.index', compact('items'));
    }

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

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity'              => 'required|integer|min:1|max:999',
            'allow_waiting_restock' => 'nullable|boolean',
        ]);

        $item = CartItem::with(['product', 'variant'])->findOrFail($id);
        $cart = Cart::where('user_id', Auth::id())->firstOrFail();
        if ($item->cart_id !== $cart->id) {
            abort(403, 'Akses ditolak.');
        }

        $restockStatus = $this->calculateRestockStatus(
            $item->product,
            (int) $request->quantity,
            $request->boolean('allow_waiting_restock'),
            $item->variant
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

    public function remove($id)
    {
        $item = CartItem::findOrFail($id);
        $cart = Cart::where('user_id', Auth::id())->firstOrFail();
        if ($item->cart_id !== $cart->id) {
            abort(403, 'Akses ditolak.');
        }

        $item->delete();
        return back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

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

        return redirect()->route('checkout.index')->with('success', 'Produk siap dibeli. Silakan lanjut checkout.');
    }
}