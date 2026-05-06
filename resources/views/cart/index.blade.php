@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="cart-page-header">
            <div>
                <span class="cart-page-badge">Keranjang Belanja</span>
                <h2>Keranjang Kamu</h2>
                <p>Periksa kembali produk sebelum lanjut ke checkout.</p>
            </div>

            @if($items->count())
                <a href="{{ route('products.index') }}" class="cart-continue-link">
                    <i class="fas fa-arrow-left"></i>
                    Lanjut Belanja
                </a>
            @endif
        </div>

        @if($items->count())
            @php
                $grandTotal = 0;
            @endphp

            <div class="cart-modern-layout">
                <div class="cart-items-card">
                    @foreach($items as $item)
                        @php
                            $price = $item->variant ? $item->variant->price : $item->product->price;
                            $subtotal = $price * $item->quantity;
                            $grandTotal += $subtotal;
                        @endphp

                        <div class="cart-modern-item">
                            <div class="cart-product-thumb">
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}">
                                @else
                                    <div class="cart-thumb-placeholder">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="cart-product-main">
                                <div class="cart-product-title-row">
                                    <div>
                                        <h3>{{ $item->product->name ?? 'Produk tidak tersedia' }}</h3>

                                        @if($item->variant)
                                            <span class="cart-variant-pill">
                                                {{ $item->variant->variant_name }}
                                            </span>
                                        @else
                                            <span class="cart-variant-pill muted">
                                                Tanpa varian
                                            </span>
                                        @endif
                                    </div>

                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="cart-remove-btn" title="Hapus produk">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <div class="cart-product-meta">
                                    <div>
                                        <span>Harga</span>
                                        <strong>Rp {{ number_format($price, 0, ',', '.') }}</strong>
                                    </div>

                                    <div>
                                        <span>Subtotal</span>
                                        <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                                    </div>
                                </div>

                                <div class="cart-product-footer">
                                    <form action="{{ route('cart.update', $item->id) }}" method="POST" class="cart-qty-modern-form">
                                        @csrf

                                        <div class="cart-quantity-control">
                                            <button type="button" class="cart-qty-btn cart-minus">−</button>

                                            <input
                                                type="number"
                                                name="quantity"
                                                value="{{ $item->quantity }}"
                                                min="1"
                                                class="cart-qty-input"
                                                readonly
                                            >

                                            <button type="button" class="cart-qty-btn cart-plus">+</button>
                                        </div>

                                        <button type="submit" class="cart-update-btn">
                                            Update
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <aside class="cart-summary-modern">
                    <div class="cart-summary-header">
                        <span>Ringkasan Belanja</span>
                        <h3>Total Pesanan</h3>
                    </div>

                    <div class="cart-summary-row">
                        <span>Jumlah Item</span>
                        <strong>{{ $items->sum('quantity') }} item</strong>
                    </div>

                    <div class="cart-summary-row">
                        <span>Subtotal Produk</span>
                        <strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong>
                    </div>

                    <div class="cart-summary-note">
                        <i class="fas fa-circle-info"></i>
                        <p>Ongkir akan dihitung pada halaman checkout khusus area Bekasi Timur.</p>
                    </div>

                    <div class="cart-summary-grand">
                        <span>Total Belanja</span>
                        <strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong>
                    </div>

                    <div class="cart-summary-actions">
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary cart-checkout-btn">
                            Lanjut Checkout
                        </a>

                        <a href="{{ route('products.index') }}" class="btn btn-light cart-shop-btn">
                            Tambah Produk Lagi
                        </a>
                    </div>
                </aside>
            </div>
        @else
            <div class="empty-state cart-empty-modern">
                <div class="empty-icon">🛒</div>
                <h3>Keranjang masih kosong</h3>
                <p>Yuk pilih produk favoritmu terlebih dahulu.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Belanja Sekarang</a>
            </div>
        @endif
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cart-qty-modern-form').forEach(function (form) {
        const input = form.querySelector('.cart-qty-input');
        const minusBtn = form.querySelector('.cart-minus');
        const plusBtn = form.querySelector('.cart-plus');

        if (!input || !minusBtn || !plusBtn) return;

        minusBtn.addEventListener('click', function () {
            let currentValue = parseInt(input.value) || 1;
            let minValue = parseInt(input.getAttribute('min')) || 1;

            if (currentValue > minValue) {
                input.value = currentValue - 1;
            }
        });

        plusBtn.addEventListener('click', function () {
            let currentValue = parseInt(input.value) || 1;
            input.value = currentValue + 1;
        });
    });
});
</script>
@endsection