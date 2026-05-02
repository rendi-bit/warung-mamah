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
                    <span class="detail-category">{{ $product->category->category_name ?? 'Kategori' }}</span>
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

                <p class="detail-short-desc">
                    {{ \Illuminate\Support\Str::limit($product->description ?? 'Produk pilihan dari Toko Tika dengan kualitas terbaik untuk kebutuhan harian.', 150) }}
                </p>

                <div class="detail-meta-box">
                    <div class="detail-meta-item">
                        <span class="meta-label">Stok</span>
                        <strong id="stock-display">{{ $product->stock_quantity }}</strong>
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
                    <form action="{{ route('cart.add', $product->id) }}" method="POST" class="detail-cart-form">
                        @csrf

                        @if($product->variants->count())
                            <div class="qty-box">
                                <label for="variant_id">Pilih Berat</label>
                                <select name="variant_id" id="variant_id" required>
                                    <option value="">-- Pilih Varian --</option>

                                    @foreach($product->variants as $variant)
                                        <option
                                            value="{{ $variant->id }}"
                                            data-price="{{ $variant->price }}"
                                            data-stock="{{ $variant->stock }}"
                                        >
                                            {{ $variant->variant_name }} - Rp {{ number_format($variant->price, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="qty-box">
                            <label for="quantity">Jumlah</label>
                            <input
                                type="number"
                                name="quantity"
                                id="quantity"
                                value="1"
                                min="1"
                                max="{{ $product->stock_quantity }}"
                                required
                            >
                        </div>

                        <div class="detail-action-box">
                            <button
                                type="submit"
                                formaction="{{ route('cart.add', $product->id) }}"
                                class="btn btn-light detail-btn-secondary"
                            >
                                Tambah ke Keranjang
                            </button>

                            <button
                                type="submit"
                                formaction="{{ route('cart.buyNow', $product->id) }}"
                                class="btn btn-primary detail-btn-main"
                            >
                                Beli Sekarang
                            </button>

                            <a href="{{ route('products.index') }}" class="btn btn-light detail-btn-secondary">
                                Kembali
                            </a>
                        </div>
                    </form>

                    <form action="{{ route('wishlist.add', $product->id) }}" method="POST" class="wishlist-form-detail">
                        @csrf
                        <button type="submit" class="btn btn-light">
                            ❤️ Tambah ke Favorit
                        </button>
                    </form>
                @else
                    <div class="detail-action-box">
                        <a href="{{ route('login') }}" class="btn btn-primary detail-btn-main">
                            Login untuk Belanja
                        </a>

                        <a href="{{ route('products.index') }}" class="btn btn-light detail-btn-secondary">
                            Kembali
                        </a>
                    </div>
                @endauth

                <div class="product-info-tabs">
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
                                <strong>{{ $product->stock_quantity }}</strong>
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
                                <span class="review-date">
                                    {{ $review->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>
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
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const variantSelect = document.getElementById('variant_id');
    const priceElement = document.getElementById('product-price');
    const stockElement = document.getElementById('stock-display');
    const quantityInput = document.getElementById('quantity');

    if (variantSelect) {
        variantSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const stock = selectedOption.getAttribute('data-stock');

            if (price) {
                const formatted = new Intl.NumberFormat('id-ID').format(price);
                priceElement.innerText = "Rp " + formatted;
            }

            if (stock) {
                stockElement.innerText = stock;
                quantityInput.max = stock;
                quantityInput.value = 1;
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
            document.getElementById('tab-' + tab).classList.add('active');
        });
    });
});
</script>
@endsection