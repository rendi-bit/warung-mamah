<x-guest-layout>
    <div class="auth-premium-card">
        <div class="auth-premium-left">
            <div class="auth-brand-badge">WM</div>
            <span class="auth-badge">Welcome Back</span>
            <h1>Masuk ke <span>Warung Mamah</span></h1>
            <p>
                Kelola pesanan, keranjang, dan aktivitas belanja kamu dari satu dashboard
                dengan pengalaman yang modern, cepat, dan nyaman.
            </p>

            <div class="auth-feature-list">
                <div class="auth-feature-item">
                    <i class="fas fa-bag-shopping"></i>
                    <span>Kelola pesanan dengan mudah</span>
                </div>
                <div class="auth-feature-item">
                    <i class="fas fa-cart-shopping"></i>
                    <span>Akses keranjang dan checkout cepat</span>
                </div>
                <div class="auth-feature-item">
                    <i class="fas fa-shield-heart"></i>
                    <span>Akun aman dan pengalaman premium</span>
                </div>
            </div>
        </div>

        <div class="auth-premium-right">
            <div class="auth-form-header">
                <h2>Login Akun</h2>
                <p>Silakan masuk untuk melanjutkan ke akun kamu.</p>
            </div>

            @if(session('status'))
                <div class="auth-alert success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="auth-alert error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="auth-premium-form">
                @csrf

                <div class="auth-input-group">
                    <label for="email">Email</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Masukkan email kamu"
                        >
                    </div>
                </div>

                <div class="auth-input-group">
                    <label for="password">Password</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-lock"></i>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan password"
                        >
                    </div>
                </div>

                <div class="auth-row">
                    <label class="auth-checkbox">
                        <input id="remember_me" type="checkbox" name="remember">
                        <span>Ingat saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="auth-link" href="{{ route('password.request') }}">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="auth-submit-btn">
                    Masuk Sekarang
                </button>

                <p class="auth-switch">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="auth-link">Daftar sekarang</a>
                </p>
            </form>
        </div>
    </div>
</x-guest-layout>