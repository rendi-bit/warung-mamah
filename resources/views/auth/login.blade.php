<x-guest-layout>
    <div class="auth-panel-head">
        <span class="auth-mini-badge">Welcome Back</span>
        <h1>Masuk ke akun kamu</h1>
        <p>
            Login untuk berbelanja kebutuhan rumah tangga dan semua aktivitas belanja ada di dalam Toko Tika
        </p>
    </div>

    <x-auth-session-status class="auth-alert-success" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="auth-form-premium">
        @csrf

        <div class="auth-form-group">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                id="email"
                class="auth-input-premium"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="Masukkan email kamu"
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
                autocomplete="current-password"
                placeholder="Masukkan password"
            />
            <x-input-error :messages="$errors->get('password')" class="auth-error-text" />
        </div>

        <div class="auth-row-between">
            <label for="remember_me" class="auth-check-wrap">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-text-link" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <button type="submit" class="auth-submit-btn">
            Masuk Sekarang
        </button>

        <div class="auth-divider">
            <span>atau</span>
        </div>

        <p class="auth-switch-text">
            Belum punya akun?
            <a href="{{ route('register') }}" class="auth-text-link strong">Daftar di sini</a>
        </p>
    </form>
</x-guest-layout>