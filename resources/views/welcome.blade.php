@extends('layouts.store')

@section('content')

<section class="section">
    <div class="container hero-grid">

        <div>
            <span class="hero-badge">
                Digital Commerce Platform
            </span>

            <h1 class="hero-title">
                Warung Mamah untuk <span>Skala Bisnis Modern</span>
            </h1>

            <p class="hero-desc">
                Platform UMKM dengan pengalaman belanja enterprise: katalog terstruktur,
                monitoring transaksi, dan pengelolaan produk yang efisien.
            </p>

            <div class="hero-actions">
                <a href="{{ route('home') }}" class="btn btn-primary">
                    Masuk Website
                </a>

                @guest
                    <a href="{{ route('login') }}" class="btn btn-light">
                        Login
                    </a>
                @endguest
            </div>
        </div>

        <div class="hero-card">
            <img 
                src="https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=1200&q=80" 
                alt="Welcome Warung Mamah"
            >
        </div>

    </div>
</section>

@endsection