@extends('layouts.store')

@section('content')

<section class="products-hero">
    <div class="container">
        <div class="products-hero-box">
            <div>
                <span class="products-hero-badge">Katalog Produk</span>
                <h1>Temukan Produk Terbaik dari Toko Tika</h1>
                <p>
                    Jelajahi berbagai produk UMKM pilihan dengan kualitas terbaik,
                    harga terjangkau, dan pengalaman belanja yang nyaman.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section" style="padding-top: 30px;">
    <div class="container">

        <div class="filter-box">
            <div class="filter-box-header">
                <div>
                    <h2>Filter Produk</h2>
                    <p>Pilih kategori untuk menampilkan produk yang sesuai.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('products.index') }}" class="filter-form">
                <div class="filter-group">
                    <label for="category">Kategori</label>
                    <select name="category" id="category">
                        <option value="">Semua Kategori</option>

                        @foreach ($categories as $category)
                            <option 
                                value="{{ $category->id }}" 
                                {{ request('category') == $category->id ? 'selected' : '' }}
                            >
                                {{ $category->category_name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        Terapkan Filter
                    </button>

                    <a href="{{ route('products.index') }}" class="btn btn-light">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="products-header-row">
            <div>
                <h2 class="products-heading">Semua Produk</h2>
                <p class="text-muted">
                    Menampilkan produk pilihan Toko Tika.
                </p>
            </div>
        </div>

        @if ($products->count())
            <div class="product-grid">

                @foreach ($products as $product)
                    <div class="product-card">

                        <div class="product-card-image">
                            @if ($product->image)
                                <img 
                                    src="{{ asset('storage/' . $product->image) }}" 
                                    alt="{{ $product->name }}"
                                >
                            @else
                                <img 
                                    src="https://via.placeholder.com/400x300?text=Produk" 
                                    alt="{{ $product->name }}"
                                >
                            @endif
                        </div>

                        <div class="product-card-body">

                            <div class="product-top-row">
                                <span class="product-category">
                                    {{ $product->category->category_name ?? '-' }}
                                </span>

                                <span class="product-badge">Ready</span>
                            </div>

                            <h3 class="product-title">
                                {{ $product->name }}
                            </h3>

                            <div class="product-price">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </div>

                            <div class="product-meta">
                                Stok tersedia: {{ $product->stock_quantity }}
                            </div>

                            <div class="product-card-actions">
                                <a 
                                    href="{{ route('products.show', $product->slug) }}" 
                                    class="btn btn-primary"
                                >
                                    Lihat Detail
                                </a>
                            </div>

                        </div>
                    </div>
                @endforeach

            </div>

            @if ($products->hasPages())
                <div class="premium-pagination-wrap">

                    <div class="premium-pagination-info">
                        Menampilkan 
                        {{ $products->firstItem() }} - {{ $products->lastItem() }}
                        dari {{ $products->total() }} produk
                    </div>

                    <div class="premium-pagination">

                        @if ($products->onFirstPage())
                            <span class="page-btn disabled">Sebelumnya</span>
                        @else
                            <a 
                                href="{{ $products->previousPageUrl() }}" 
                                class="page-btn"
                            >
                                Sebelumnya
                            </a>
                        @endif

                        @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                            @if ($page == $products->currentPage())
                                <span class="page-number active">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="page-number">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        @if ($products->hasMorePages())
                            <a 
                                href="{{ $products->nextPageUrl() }}" 
                                class="page-btn"
                            >
                                Berikutnya
                            </a>
                        @else
                            <span class="page-btn disabled">Berikutnya</span>
                        @endif

                    </div>
                </div>
            @endif

        @endif

        <div class="products-bottom-cta">
            <div class="products-bottom-cta-box">

                <span class="products-bottom-badge">
                    Masih Cari Produk?
                </span>

                <h3>
                    Temukan lebih banyak pilihan terbaik di Toko Tika
                </h3>

                <p>
                    Jelajahi kategori produk lainnya, simpan favoritmu, dan lanjutkan belanja
                    dengan pengalaman yang nyaman dan modern.
                </p>

                <div class="products-bottom-actions">
                    <a 
                        href="{{ route('products.index') }}" 
                        class="btn btn-primary"
                    >
                        Lihat Semua Produk
                    </a>

                    <a 
                        href="{{ route('cart.index') }}" 
                        class="btn btn-light"
                    >
                        Buka Keranjang
                    </a>
                </div>

            </div>
        </div>

    </div>
</section>

@endsection