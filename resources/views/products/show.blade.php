@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <a href="{{ route('products.index') }}">Produk</a>
            <span>/</span>
            <span>{{ $product->name }}</span>
        </div>

        <div class="product-detail-grid">
            <div class="product-detail-image-box">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                @else
                    <img src="https://via.placeholder.com/700x600?text=Produk" alt="{{ $product->name }}">
                @endif
            </div>

            <div class="product-detail-info">
                <div class="detail-category-row">
                    <span class="detail-category">
                        {{ $product->category->category_name ?? 'Kategori' }}
                    </span>
                    <span class="detail-status">Ready Stock</span>
                </div>

                <h1 class="detail-title">{{ $product->name }}</h1>

                <div class="detail-price" id="product-price">
                    @if($product->variants->count())
                        Mulai dari Rp {{ number_format($product->display_price, 0, ',', '.') }}
                    @else
                        Rp {{ number_format($product->display_price, 0, ',', '.') }}
                    @endif
                </div>

                <div class="detail-meta-box">
                    <div class="detail-meta-item">
                        <span class="meta-label">Stok</span>
                        <strong id="stock-display">{{ $product->stock_label }}</strong>
                    </div>
                    <div class="detail-meta-item">
                        <span class="meta-label">Kategori</span>
                        <strong>{{ $product->category->category_name ?? '-' }}</strong>
                    </div>
                    <div class="detail-meta-item">
                        <span class="meta-label">Kondisi</span>
                        <strong>Baru</strong>
                    </div>
                </div>

                @auth
                    <form
                        id="detailCartForm"
                        action="{{ route('cart.add', $product->id) }}"
                        method="POST"
                        class="detail-cart-form"
                    >
                        @csrf
                        <input type="hidden" name="allow_waiting_restock" id="allow_waiting_restock" value="0">

                        @if($product->variants->count())
                            <div class="qty-box">
                                <label for="variant_id">Pilih Berat</label>
                                <select name="variant_id" id="variant_id" required>
                                    <option value="">-- Pilih Varian --</option>
                                    @foreach($product->variants as $variant)
                                        <option value="{{ $variant->id }}" data-price="{{ $variant->price }}">
                                            {{ $variant->variant_name }} - Rp {{ number_format($variant->price, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="qty-box">
                            <label for="quantity">Jumlah</label>
                            <div class="quantity-control">
                                <button type="button" class="qty-btn qty-minus">−</button>
                                <input
                                    type="number"
                                    name="quantity"
                                    id="quantity"
                                    class="qty-input"
                                    value="1"
                                    min="1"
                                    readonly
                                    required
                                >
                                <button type="button" class="qty-btn qty-plus">+</button>
                            </div>
                        </div>
                    </form>

                    <div class="product-action-modern">
                        <button
                            type="submit"
                            form="detailCartForm"
                            formaction="{{ route('cart.add', $product->id) }}"
                            class="icon-action-btn cart-action-btn"
                            title="Tambah ke Keranjang"
                            aria-label="Tambah ke Keranjang"
                        >
                            <i class="fas fa-cart-shopping"></i>
                        </button>

                        <button
                            type="submit"
                            form="detailCartForm"
                            formaction="{{ route('cart.buyNow', $product->id) }}"
                            class="btn-buy-now-modern"
                        >
                            Beli Sekarang
                        </button>

                        <a href="{{ route('products.index') }}" class="btn-back-modern">
                            Kembali
                        </a>
                    </div>
                @else
                    <div class="product-action-modern">
                        <a href="{{ route('login') }}" class="btn-buy-now-modern">
                            Login untuk Belanja
                        </a>
                        <a href="{{ route('products.index') }}" class="btn-back-modern">
                            Kembali
                        </a>
                    </div>
                @endauth
            </div>
        </div>

        {{-- Tab: Deskripsi & Layanan saja --}}
        <div class="product-info-tabs product-info-tabs-full">
            <div class="tab-buttons">
                <button type="button" class="tab-btn active" data-tab="desc">Deskripsi</button>
                <button type="button" class="tab-btn" data-tab="service">Layanan</button>
            </div>

            <div class="tab-content active" id="tab-desc">
                <p>{{ $product->description ?? 'Produk pilihan dari Toko Tika dengan kualitas terbaik untuk kebutuhan harian.' }}</p>
            </div>

            <div class="tab-content" id="tab-service">
                <div class="service-wrapper">

                    <h4 class="service-title">Metode Pembayaran</h4>

                    <div class="service-grid">
                        <div class="service-card">
                            <div class="service-icon">📱</div>

                            <div>
                                <h5>QRIS</h5>
                                <p>Pembayaran mudah menggunakan QRIS.</p>
                            </div>
                        </div>

                        <div class="service-card">
                            <div class="service-icon">💵</div>

                            <div>
                                <h5>COD</h5>
                                <p>Bayar langsung saat pesanan diterima.</p>
                            </div>
                        </div>
                    </div>

                    <h4 class="service-title" style="margin-top: 28px;">
                        Pengantaran / Pengambilan
                    </h4>

                    <div class="service-grid">
                        <div class="service-card">
                            <div class="service-icon">🛵</div>

                            <div>
                                <h5>Diantar Ojek Warung</h5>
                                <p>Pesanan diantar menggunakan ojek warung.</p>
                            </div>
                        </div>

                        <div class="service-card">
                            <div class="service-icon">🏪</div>

                            <div>
                                <h5>Ambil ke Warung</h5>
                                <p>Ambil pesanan langsung ke warung kami.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        {{-- Produk Terkait --}}
        @if($relatedProducts->count())
        <section style="margin-top: 40px;">
            <h3 style="margin-bottom: 16px;">Produk Terkait</h3>
            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
                @foreach($relatedProducts as $related)
                    <a href="{{ route('products.show', $related->slug) }}" class="product-card" style="text-decoration:none;">
                        <div class="product-card-image">
                            @if($related->image)
                                <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->name }}">
                            @else
                                <i class="fas fa-box-open"></i>
                            @endif
                        </div>
                        <div class="product-card-body">
                            <h4>{{ $related->name }}</h4>
                            <p>Rp {{ number_format($related->display_price ?? $related->price, 0, ',', '.') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

    </div>
</section>

{{-- Modal Stok --}}
<div class="stock-warning-modal" id="stockWarningModal">
    <div class="stock-warning-card">
        <button type="button" class="stock-warning-close" id="stockWarningClose">×</button>
        <div class="stock-warning-icon">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <h3>Stok Belum Mencukupi</h3>
        <p>
            Maaf, stok <strong>{{ $product->name }}</strong> saat ini hanya
            <strong>{{ $product->stock_quantity }} {{ $product->stock_unit }}</strong>.
        </p>
        <p>
            Produk sedang dalam proses restok dan diperkirakan tersedia dalam
            <strong>{{ $product->restock_estimation ?? '1 hari' }}</strong>.
        </p>
        <div class="stock-warning-actions">
            <button type="button" class="btn btn-light" id="reduceToStockBtn">
                Kurangi ke {{ $product->stock_quantity }} {{ $product->stock_unit }}
            </button>
            <button type="button" class="btn btn-primary" id="waitRestockBtn">
                Tetap Pesan & Tunggu Restok
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const variantSelect = document.getElementById('variant_id');
    const priceElement  = document.getElementById('product-price');

    if (variantSelect && priceElement) {
        variantSelect.addEventListener('change', function () {
            const price = this.options[this.selectedIndex].getAttribute('data-price');
            if (price) {
                priceElement.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
            }
        });
    }

    const qtyInput = document.getElementById('quantity');
    const minusBtn = document.querySelector('.qty-minus');
    const plusBtn  = document.querySelector('.qty-plus');

    if (qtyInput && minusBtn && plusBtn) {
        minusBtn.addEventListener('click', function () {
            const min = parseInt(qtyInput.getAttribute('min')) || 1;
            const val = parseInt(qtyInput.value) || 1;
            if (val > min) qtyInput.value = val - 1;
        });
        plusBtn.addEventListener('click', function () {
            qtyInput.value = (parseInt(qtyInput.value) || 1) + 1;
        });
    }

    const detailCartForm      = document.getElementById('detailCartForm');
    const stockWarningModal   = document.getElementById('stockWarningModal');
    const stockWarningClose   = document.getElementById('stockWarningClose');
    const reduceToStockBtn    = document.getElementById('reduceToStockBtn');
    const waitRestockBtn      = document.getElementById('waitRestockBtn');
    const allowWaitingRestock = document.getElementById('allow_waiting_restock');
    const availableStock      = {{ (int) $product->stock_quantity }};
    let pendingSubmitter      = null;

    if (detailCartForm && qtyInput && stockWarningModal) {
        detailCartForm.addEventListener('submit', function (event) {
            const requestedQty = parseInt(qtyInput.value) || 1;
            if (requestedQty > availableStock && allowWaitingRestock?.value !== '1') {
                event.preventDefault();
                pendingSubmitter = event.submitter;
                stockWarningModal.classList.add('active');
            }
        });
    }

    if (stockWarningClose) {
        stockWarningClose.addEventListener('click', () => stockWarningModal.classList.remove('active'));
    }

    if (reduceToStockBtn) {
        reduceToStockBtn.addEventListener('click', function () {
            qtyInput.value = availableStock > 0 ? availableStock : 1;
            stockWarningModal.classList.remove('active');
        });
    }

    if (waitRestockBtn && allowWaitingRestock) {
        waitRestockBtn.addEventListener('click', function () {
            allowWaitingRestock.value = '1';
            stockWarningModal.classList.remove('active');
            pendingSubmitter ? pendingSubmitter.click() : detailCartForm.submit();
        });
    }

    document.querySelectorAll('.tab-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            button.classList.add('active');
            const target = document.getElementById('tab-' + button.dataset.tab);
            if (target) target.classList.add('active');
        });
    });
});
</script>
@endsection