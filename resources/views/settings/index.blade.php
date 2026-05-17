@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Pengaturan Akun</h2>
            <p>Kelola informasi profil dan lihat ringkasan aktivitas akunmu.</p>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div style="max-width:800px;margin:0 auto 16px;padding:14px 20px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;color:#065f46;font-weight:500;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="max-width:800px;margin:0 auto 16px;padding:14px 20px;background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;color:#991b1b;font-weight:500;">
                ❌ {{ session('error') }}
            </div>
        @endif

        <div class="settings-layout">
            <div class="settings-sidebar">
                <div class="settings-user-card">
                    <div class="settings-avatar">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ $user->name }}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <h3>{{ $user->name }}</h3>
                    <p>{{ $user->email }}</p>
                </div>

                <div class="settings-menu">
                    <a href="#profile" class="settings-menu-item active">Profil</a>
                    <a href="#password" class="settings-menu-item">Ganti Password</a>
                    <a href="#analytics" class="settings-menu-item">Mini Analytics</a>
                </div>
            </div>

            <div class="settings-content">

                {{-- ============================= --}}
                {{-- FORM EDIT PROFIL              --}}
                {{-- ============================= --}}
                <div id="profile" class="settings-card">
                    <div class="settings-card-header">
                        <h3>Edit Profil Akun</h3>
                        <p>Perbarui informasi dasar akun pengguna.</p>
                    </div>

                    <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data" class="settings-form">
                        @csrf
                        @method('PUT')

                        <div class="profile-upload-section">
                            <div class="settings-input-group">
                                <label for="avatar">Foto Profil</label>

                                <div class="profile-upload-box">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="profile-preview-image">
                                    @else
                                        <div class="profile-preview-placeholder">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="profile-upload-info">
                                        <p>Format: JPG, PNG, WEBP. Maksimal 2MB.</p>
                                        <input type="file" name="avatar" id="avatar" accept="image/*" class="profile-file-input">
                                        @error('avatar')
                                            <small style="color:#dc2626;">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="profile-form-grid">
                            <div class="settings-input-group">
                                <label for="name">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <small style="color:#dc2626;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="settings-input-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <small style="color:#dc2626;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="settings-input-group">
                                <label for="phone">Nomor Telepon / WhatsApp</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 082125052233">
                                @error('phone')
                                    <small style="color:#dc2626;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="settings-input-group full-width">
                                <label for="address">Alamat</label>
                                <textarea name="address" id="address" rows="4" placeholder="Alamat lengkap untuk pengiriman">{{ old('address', $user->address) }}</textarea>
                                @error('address')
                                    <small style="color:#dc2626;">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="settings-form-actions">
                            <button type="submit" class="btn btn-primary settings-save-btn">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ============================= --}}
                {{-- ✅ FIX #1: FORM GANTI PASSWORD --}}
                {{-- Route diubah ke settings.password.update --}}
                {{-- ============================= --}}
                <div id="password" class="settings-card" style="margin-top:24px;">
                    <div class="settings-card-header">
                        <h3>Ganti Password</h3>
                        <p>Pastikan password baru minimal 8 karakter.</p>
                    </div>

                    <form action="{{ route('settings.password.update') }}" method="POST" class="settings-form">
                        @csrf
                        @method('PUT')

                        <div class="profile-form-grid">
                            <div class="settings-input-group full-width">
                                <label for="current_password">Password Lama</label>
                                <input type="password" name="current_password" id="current_password" required autocomplete="current-password">
                                @error('current_password')
                                    <small style="color:#dc2626;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="settings-input-group">
                                <label for="password">Password Baru</label>
                                <input type="password" name="password" id="password" required autocomplete="new-password">
                                @error('password')
                                    <small style="color:#dc2626;">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="settings-input-group">
                                <label for="password_confirmation">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="settings-form-actions">
                            <button type="submit" class="btn btn-primary">
                                Simpan Password Baru
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ============================= --}}
                {{-- MINI ANALYTICS               --}}
                {{-- ============================= --}}
                <div id="analytics" class="settings-card" style="margin-top:24px;">
                    <div class="settings-card-header">
                        <h3>Mini Analytics Kamu</h3>
                        <p>Ringkasan aktivitas akunmu di platform.</p>
                    </div>

                    <div class="mini-analytics-grid">
                        <div class="mini-analytics-card">
                            <span>Total Pesanan</span>
                            <h4>{{ $totalOrders }}</h4>
                            <p>Jumlah semua transaksi yang pernah dibuat.</p>
                        </div>

                        <div class="mini-analytics-card">
                            <span>Pesanan Menunggu</span>
                            <h4>{{ $pendingOrders }}</h4>
                            <p>Order yang masih menunggu konfirmasi.</p>
                        </div>

                        <div class="mini-analytics-card">
                            <span>Total Belanja</span>
                            <h4>Rp {{ number_format($totalSpent, 0, ',', '.') }}</h4>
                            <p>Akumulasi nilai pembelian yang sudah dibayar.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection