<x-guest-layout>
    <div class="auth-panel-head">
        <span class="auth-mini-badge">Reset Password</span>

        <h1>Lupa Password?</h1>

        <p>
            Masukkan email akun kamu. Kami akan mengirimkan link reset password ke email tersebut.
        </p>
    </div>

    <x-auth-session-status class="auth-alert-success" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="auth-form-premium">
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
                placeholder="Masukkan email akun kamu"
            />

            <x-input-error :messages="$errors->get('email')" class="auth-error-text" />
        </div>

        <button type="submit" class="auth-submit-btn">
            Kirim Link Reset Password
        </button>

        <p class="auth-switch-text">
            Sudah ingat password?
            <a href="{{ route('login') }}" class="auth-text-link strong">
                Kembali ke login
            </a>
        </p>
    </form>
</x-guest-layout>