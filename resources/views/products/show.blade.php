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

                    <span class="detail-status">
                        Ready Stock
                    </span>
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
                                        <option
                                            value="{{ $variant->id }}"
                                            data-price="{{ $variant->price }}"
                                        >
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

                        <form action="{{ route('wishlist.add', $product->id) }}" method="POST" class="favorite-modern-form">
                            @csrf

                            <button
                                type="submit"
                                class="icon-action-btn favorite-action-btn"
                                title="Tambah ke Favorit"
                                aria-label="Tambah ke Favorit"
                            >
                                <i class="fas fa-heart"></i>
                            </button>
                        </form>

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

        <div class="product-info-tabs product-info-tabs-full">
            <div class="tab-buttons">
                <button type="button" class="tab-btn active" data-tab="desc">Deskripsi</button>
                <button type="button" class="tab-btn" data-tab="info">Info Produk</button>
                <button type="button" class="tab-btn" data-tab="service">Layanan</button>
            </div>

            <div class="tab-content active" id="tab-desc">
                <p>
                    {{ $product->description ?? 'Produk pilihan dari Toko Tika dengan kualitas terbaik untuk kebutuhan harian.' }}
                </p>
            </div>

            <div class="tab-content" id="tab-info">
                <div class="info-list">
                    <div>
                        <span>Nama Produk</span>
                        <strong>{{ $product->name }}</strong>
                    </div>

                    <div>
                        <span>Kategori</span>
                        <strong>{{ $product->category->category_name ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Stok</span>
                        <strong>{{ $product->stock_label }}</strong>
                    </div>

                    <div>
                        <span>Status</span>
                        <strong>{{ ucfirst($product->status ?? 'active') }}</strong>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="tab-service">
                <div class="service-list">
                    <div>✅ Produk berkualitas dan aman digunakan</div>
                    <div>📦 Pesanan diproses dengan teliti</div>
                    <div>🛒 Cocok untuk kebutuhan rumah tangga</div>
                    <div>🤎 Mendukung UMKM lokal</div>
                </div>
            </div>
        </div>

        <section class="review-section">
            <div class="review-card-premium">
                <div class="review-heading">
                    <div>
                        <span class="review-badge">Ulasan Pelanggan</span>
                        <h2>Review & Rating Produk</h2>
                        <p>Lihat pengalaman pelanggan setelah membeli produk ini.</p>
                    </div>
                </div>

                <div class="review-layout">
                    <div class="review-summary-premium">
                        <h1>{{ number_format($avgRating ?? 0, 1) }}</h1>

                        <div class="stars review-stars-big">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= round($avgRating ?? 0) ? 'active' : '' }}">★</span>
                            @endfor
                        </div>

                        <p>{{ $reviews->count() }} ulasan pelanggan</p>
                    </div>

                    @auth
                        <div class="review-form-premium">
                            <h3>Tulis Review Kamu</h3>

                            <form action="{{ route('products.reviews.store', $product->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <label>Rating Produk</label>
                                <div class="rating-input">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" required>
                                        <label for="star{{ $i }}">★</label>
                                    @endfor
                                </div>

                                <label>Komentar</label>
                                <textarea name="comment" placeholder="Tulis pengalaman kamu setelah membeli produk ini..." rows="4"></textarea>

                                <label>Foto Review</label>
                                <input type="file" name="image" accept="image/*" class="review-file-input">

                                <button type="submit" class="btn btn-primary">
                                    Kirim Review
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="review-form-premium">
                            <h3>Mau kasih review?</h3>
                            <p class="text-muted">Login dulu untuk memberikan rating dan ulasan produk.</p>
                            <a href="{{ route('login') }}" class="btn btn-primary">Login Sekarang</a>
                        </div>
                    @endauth
                </div>

                <div class="review-list-premium">
                    @forelse($reviews as $review)
                        <div class="review-item-premium">
                            <div class="review-user">
                                <div class="review-avatar">
                                    {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                                </div>

                                <div>
                                    <strong>{{ $review->user->name ?? 'User' }}</strong>

                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= $review->rating ? 'active' : '' }}">★</span>
                                        @endfor
                                    </div>

                                    <span class="review-date">
                                        {{ $review->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>
                            </div>

                            <p>{{ $review->comment ?: 'Tidak ada komentar.' }}</p>

                            @if($review->image)
                                <div class="review-image-box">
                                    <img src="{{ asset('storage/' . $review->image) }}" alt="Foto review">
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="review-empty">
                            <div>⭐</div>
                            <h3>Belum ada review</h3>
                            <p>Jadilah pelanggan pertama yang memberikan ulasan produk ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</section>

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
    const priceElement = document.getElementById('product-price');

    if (variantSelect && priceElement) {
        variantSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');

            if (price) {
                const formatted = new Intl.NumberFormat('id-ID').format(price);
                priceElement.innerText = "Rp " + formatted;
            }
        });
    }

    const qtyInput = document.getElementById('quantity');
    const minusBtn = document.querySelector('.qty-minus');
    const plusBtn = document.querySelector('.qty-plus');

    if (qtyInput && minusBtn && plusBtn) {
        minusBtn.addEventListener('click', function () {
            let currentValue = parseInt(qtyInput.value) || 1;
            let minValue = parseInt(qtyInput.getAttribute('min')) || 1;

            if (currentValue > minValue) {
                qtyInput.value = currentValue - 1;
            }
        });

        plusBtn.addEventListener('click', function () {
            let currentValue = parseInt(qtyInput.value) || 1;
            qtyInput.value = currentValue + 1;
        });
    }

    const detailCartForm = document.getElementById('detailCartForm');
    const stockWarningModal = document.getElementById('stockWarningModal');
    const stockWarningClose = document.getElementById('stockWarningClose');
    const reduceToStockBtn = document.getElementById('reduceToStockBtn');
    const waitRestockBtn = document.getElementById('waitRestockBtn');
    const allowWaitingRestock = document.getElementById('allow_waiting_restock');

    const availableStock = {{ (int) $product->stock_quantity }};

    let pendingSubmitter = null;

    if (detailCartForm && qtyInput && stockWarningModal) {
        detailCartForm.addEventListener('submit', function (event) {
            const requestedQty = parseInt(qtyInput.value) || 1;
            const allowRestock = allowWaitingRestock ? allowWaitingRestock.value : '0';

            if (requestedQty > availableStock && allowRestock !== '1') {
                event.preventDefault();

                pendingSubmitter = event.submitter;

                stockWarningModal.classList.add('active');
            }
        });
    }

    if (stockWarningClose && stockWarningModal) {
        stockWarningClose.addEventListener('click', function () {
            stockWarningModal.classList.remove('active'); 
        });
    }

    if (reduceToStockBtn && qtyInput && stockWarningModal) {
        reduceToStockBtn.addEventListener('click', function () {
            qtyInput.value = availableStock > 0 ? availableStock : 1;
            stockWarningModal.classList.remove('active');
        });
    }

    if (waitRestockBtn && detailCartForm && allowWaitingRestock) {
        waitRestockBtn.addEventListener('click', function () {
            allowWaitingRestock.value = '1';
            stockWarningModal.classList.remove('active');

            if (pendingSubmitter) {
                pendingSubmitter.click();
            } else {
                detailCartForm.submit();
            }
        });
    }

    const buttons = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            const tab = button.dataset.tab;

            buttons.forEach(btn => btn.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));

            button.classList.add('active');

            const target = document.getElementById('tab-' + tab);
            if (target) {
                target.classList.add('active');
            }
        });
    });
});
</script>
@endsection