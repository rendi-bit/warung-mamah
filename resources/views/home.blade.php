@extends('layouts.store')

@section('content')

@php
    $myOrdersCount  = auth()->check() ? auth()->user()->orders()->count() : 0;
    $myPendingCount = auth()->check() ? auth()->user()->orders()->where('order_status', 'pending')->count() : 0;
    $mySpendTotal   = auth()->check() ? auth()->user()->orders()->sum('grand_total') : 0;
@endphp

<section class="section">
    <div class="container hero-grid">

        <div>
            <span class="hero-badge">UMKM Lokal • Modern Store</span>

            <h1 class="hero-title">
                Belanja Produk Pilihan dari <span>TOKO TIKA</span>
            </h1>

            <p class="hero-desc">
                Temukan produk kebutuhan pokok rumah tangga dari UMKM yang berkualitas dengan pengalaman belanja yang modern, nyaman, dan terpercaya.
            </p>

            <div class="hero-actions">
                <a href="{{ route('products.index') }}" class="btn btn-primary">
                    Belanja Sekarang
                </a>

                <a href="{{ route('products.index') }}" class="btn btn-light">
                    Lihat Produk
                </a>
            </div>
        </div>

        <div class="hero-card">
            <img 
                src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80" 
                alt="Hero"
            >
        </div>

    </div>
</section>


<section class="section" style="padding-top: 0;">
    <div class="container feature-grid">

        <div class="feature-card">
            <h3>Produk Berkualitas</h3>
            <p>Kami menghadirkan produk UMKM dengan kualitas terbaik untuk pelanggan.</p>
        </div>

        <div class="feature-card">
            <h3>Harga Terjangkau</h3>
            <p>Harga bersaing dan ramah untuk semua kalangan.</p>
        </div>

        <div class="feature-card">
            <h3>Belanja Mudah</h3>
            <p>Sistem pemesanan modern, cepat, dan nyaman digunakan.</p>
        </div>

        <div class="feature-card">
            <h3>Dukung UMKM</h3>
            <p>Setiap pembelian membantu usaha lokal tumbuh lebih besar.</p>
        </div>

    </div>
</section>

<section class="section">
    <div class="container">

        <div class="section-header">
            <h2>Kategori Unggulan</h2>
            <p>Beberapa kategori pilihan yang tersedia di Toko Tika.</p>
        </div>

        <div class="featured-category-grid">
            @foreach($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->id]) }}" class="featured-category-card">
                    <div class="featured-category-icon">
                        🛍️
                    </div>

                    <h3>{{ $category->category_name }}</h3>
                    <p>Kategori pilihan dengan produk terbaik.</p>
                </a>
            @endforeach
        </div>

    </div>
</section>

<section class="section">
    <div class="container">

        <div class="section-header">
            <h2>Produk Pilihan</h2>
            <p>Produk unggulan yang paling diminati pelanggan.</p>
        </div>

        @if ($products->count())
            <div class="product-grid">

                @foreach($products as $product)
                    <a href="{{ route('products.show', $product->slug) }}" class="product-card-link">
                        <div class="product-card clickable-product-card">
                            <div class="product-card-image">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <img src="https://via.placeholder.com/400x300?text=Produk" alt="{{ $product->name }}">
                                @endif
                            </div>

                            <div class="product-card-body">
                                <div class="product-top-row">
                                    <span class="product-category">{{ $product->category->category_name ?? '-' }}</span>

                                    @if($product->variants->count())
                                        <span class="product-badge">Punya Varian</span>
                                    @else
                                        <span class="product-badge">Ready</span>
                                    @endif
                                </div>

                                <h3 class="product-title">{{ $product->name }}</h3>

                                <div class="product-price">
                                    @if($product->variants->count())
                                        Mulai dari Rp {{ number_format($product->display_price, 0, ',', '.') }}
                                    @else
                                        Rp {{ number_format($product->display_price, 0, ',', '.') }}
                                    @endif
                                </div>

                                <div class="product-meta">
                                    Stok tersedia: {{ $product->stock_label ?? $product->stock_quantity }}
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach

            </div>
        @else
            <div class="feature-card">
                <p>Belum ada produk tersedia.</p>
            </div>
        @endif

    </div>
</section>

<section class="section">
    <div class="container">

        <div class="cta-box">
            <h2>Dukung UMKM Lokal Bersama Toko Tika</h2>

            <p>
                Belanja produk pilihan sambil mendukung usaha lokal berkembang lebih besar, profesional, dan terpercaya.
            </p>

            <a href="{{ route('products.index') }}" class="btn btn-light">
                Mulai Belanja
            </a>
        </div>

    </div>
</section>

@endsection