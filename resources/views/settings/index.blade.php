@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Pengaturan Akun</h2>
            <p>Kelola informasi profil dan lihat ringkasan aktivitas akunmu.</p>
        </div>

        <div class="settings-layout">
            <div class="settings-sidebar">
                <div class="settings-user-card">
                    <div class="settings-avatar">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h3>{{ $user->name }}</h3>
                    <p>{{ $user->email }}</p>
                </div>

                <div class="settings-menu">
                    <a href="#profile" class="settings-menu-item active">Profil</a>
                    <a href="#analytics" class="settings-menu-item">Mini Analytics</a>
                </div>
            </div>

            <div class="settings-content">
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
                    <p>Upload foto profil baru. Format: JPG, PNG, WEBP. Maksimal 2MB.</p>
                    <input type="file" name="avatar" id="avatar" accept="image/*" class="profile-file-input">
                </div>
            </div>
        </div>
    </div>

    <div class="profile-form-grid">
        <div class="settings-input-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="settings-input-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="settings-input-group">
            <label for="phone">Nomor Telepon</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}">
        </div>

        <div class="settings-input-group full-width">
            <label for="address">Alamat</label>
            <textarea name="address" id="address" rows="4">{{ old('address', $user->address) }}</textarea>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn btn-primary settings-save-btn">Simpan Perubahan</button>
    </div>
</form>
    <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data" class="settings-form">
        @csrf
        @method('PUT')

        <div class="profile-form-grid">
            <div class="settings-input-group">
                <label for="current_password">Password Lama</label>
                <input type="password" name="current_password" id="current_password" required>
            </div>

            <div class="settings-input-group">
                <label for="password">Password Baru</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="settings-input-group">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
    </form>
</div>

                <div id="analytics" class="settings-card">
                    <div class="settings-card-header">
                        <h3>Mini Analytics Kamu</h3>
                        <p>Ringkasan aktivitas akunmu secara real-time di platform Warung Mamah.</p>
                    </div>
    
                    <div class="mini-analytics-grid">
                        <div class="mini-analytics-card">
                            <span>Total Pesanan Kamu</span>
                            <h4>{{ $totalOrders }}</h4>
                            <p>Jumlah semua transaksi yang pernah dibuat.</p>
                        </div>

                        <div class="mini-analytics-card">
                            <span>Pesanan Menunggu</span>
                            <h4>{{ $pendingOrders }}</h4>
                            <p>Order yang masih menunggu proses konfirmasi.</p>
                        </div>

                        <div class="mini-analytics-card">
                            <span>Total Belanja</span>
                            <h4>Rp {{ number_format($totalSpent, 0, ',', '.') }}</h4>
                            <p>Akumulasi nilai pembelian akunmu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection