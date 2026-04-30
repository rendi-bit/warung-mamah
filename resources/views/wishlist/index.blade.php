@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Favorit Saya ❤️</h2>
            <p>Produk yang kamu simpan untuk dibeli nanti.</p>
        </div>

        @if($items->count())
            <div class="product-grid">
                @foreach($items as $item)
                    @php
                        $product = $item->product;
                        $price = $product->variants->count()
                            ? $product->variants->min('price')
                            : $product->price;
                    @endphp

                    <div class="product-card">
                        <div class="product-card-image">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                            @else
                                <img src="https://via.placeholder.com/400x300?text=Produk" alt="{{ $product->name }}">
                            @endif
                        </div>

                        <div class="product-card-body">
                            <div class="product-top-row">
                                <span class="product-category">
                                    {{ $product->category->category_name ?? '-' }}
                                </span>

                                <span class="product-badge">Favorit</span>
                            </div>

                            <h3 class="product-title">{{ $product->name }}</h3>

                            <div class="product-price">
                                @if($product->variants->count())
                                    Mulai dari Rp {{ number_format($price, 0, ',', '.') }}
                                @else
                                    Rp {{ number_format($price, 0, ',', '.') }}
                                @endif
                            </div>

                            <div class="product-card-actions">
                                <a href="{{ route('products.show', $product->slug) }}" class="btn btn-primary">
                                    Lihat Produk
                                </a>

                                <form action="{{ route('wishlist.remove', $item->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">❤️</div>
                <h3>Belum ada favorit</h3>
                <p>Yuk simpan produk favoritmu dulu.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">
                    Lihat Produk
                </a>
            </div>
        @endif
    </div>
</section>
@endsection