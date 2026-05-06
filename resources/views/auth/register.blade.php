<x-guest-layout>
    <div class="auth-panel-head">
        <span class="auth-mini-badge">Create Account</span>
        <h1>Buat akun baru</h1>
        <p>
            Daftar sekarang untuk mulai belanja, menyimpan favorit, dan memantau pesanan dengan mudah.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="auth-form-premium">
        @csrf

        <div class="auth-form-group">
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input
                id="name"
                class="auth-input-premium"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                placeholder="Masukkan nama lengkap"
            />
            <x-input-error :messages="$errors->get('name')" class="auth-error-text" />
        </div>

        <div class="auth-form-group">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                class="auth-input-premium"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="username"
                placeholder="Masukkan email aktif"
            />
            <x-input-error :messages="$errors->get('email')" class="auth-error-text" />
        </div>

        <div class="auth-form-group">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input
                id="password"
                class="auth-input-premium"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Masukkan password"
            />
            <x-input-error :messages="$errors->get('password')" class="auth-error-text" />
        </div>

        <div class="auth-form-group">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input
                id="password_confirmation"
                class="auth-input-premium"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Ulangi password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error-text" />
        </div>

        <button type="submit" class="auth-submit-btn">
            Daftar Sekarang
        </button>

        <div class="auth-divider">
            <span>atau</span>
        </div>

        <p class="auth-switch-text">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="auth-text-link strong">Masuk di sini</a>
        </p>
    </form>
</x-guest-layout>