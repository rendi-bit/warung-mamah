<x-guest-layout>
    <div class="auth-premium-card auth-single-card">
        <div class="auth-premium-left">
            <div class="auth-brand-badge">WM</div>
            <h1>Lupa <span>Password?</span></h1>
            <p>
                Tenang, masukkan email akun kamu dan kami akan mengirimkan
                link untuk reset password agar kamu bisa masuk kembali.
            </p>

            <div class="auth-feature-list">
                <div class="auth-feature-item">
                    <i class="fas fa-envelope-circle-check"></i>
                    <span>Link reset akan dikirim ke email kamu</span>
                </div>
                <div class="auth-feature-item">
                    <i class="fas fa-shield-heart"></i>
                    <span>Proses aman dan mudah dilakukan</span>
                </div>
            </div>
        </div>

        <div class="auth-premium-right">
            <div class="auth-form-header">
                <h2>Reset Password</h2>
                <p>Masukkan email akun kamu untuk menerima link reset password.</p>
            </div>

            @if (session('status'))
                <div class="auth-alert success">
                    {{ session('status') }}
                </div>
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

            <form method="POST" action="{{ route('password.email') }}" class="auth-premium-form">
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
                            placeholder="Masukkan email akun kamu"
                        >
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn">
                    Kirim Link Reset Password
                </button>

                <p class="auth-switch">
                    Sudah ingat password?
                    <a href="{{ route('login') }}" class="auth-link">Kembali ke login</a>
                </p>
            </form>
        </div>
    </div>
</x-guest-layout>