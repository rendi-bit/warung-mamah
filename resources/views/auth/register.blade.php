<x-guest-layout>
    <div class="auth-premium-card">
        <div class="auth-premium-left">
            <div class="auth-brand-badge">WM</div>
            
            <h1>Buat Akun <span>Warung Mamah</span></h1>
            <p>
                Daftar sekarang untuk mulai belanja, menyimpan produk favorit,
                mengelola pesanan, dan menikmati pengalaman belanja yang modern.
            </p>

            <div class="auth-feature-list">
                <div class="auth-feature-item">
                    <i class="fas fa-user-plus"></i>
                    <span>Proses pendaftaran cepat dan mudah</span>
                </div>
                <div class="auth-feature-item">
                    <i class="fas fa-heart"></i>
                    <span>Simpan produk favorit kamu</span>
                </div>
                <div class="auth-feature-item">
                    <i class="fas fa-bag-shopping"></i>
                    <span>Checkout dan lacak pesanan dengan nyaman</span>
                </div>
            </div>
        </div>

        <div class="auth-premium-right">
            <div class="auth-form-header">
                <h2>Daftar Akun</h2>
                <p>Isi data di bawah ini untuk membuat akun baru.</p>
            </div>

            @if($errors->any())
                <div class="auth-alert error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="auth-premium-form">
                @csrf

                <div class="auth-input-group">
                    <label for="name">Nama Lengkap</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-user"></i>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Masukkan nama lengkap"
                        >
                    </div>
                </div>

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
                            autocomplete="username"
                            placeholder="Masukkan email"
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
                            autocomplete="new-password"
                            placeholder="Masukkan password"
                        >
                    </div>
                </div>

                <div class="auth-input-group">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-shield-halved"></i>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Ulangi password"
                        >
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn">
                    Daftar Sekarang
                </button>

                <p class="auth-switch">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="auth-link">Masuk di sini</a>
                </p>
            </form>
        </div>
    </div>
</x-guest-layout>