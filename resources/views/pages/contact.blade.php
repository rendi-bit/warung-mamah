@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="info-page-hero">
            <span class="info-page-badge">Kontak Kami</span>
            <h1>Hubungi TOKO TIKA</h1>
            <p>Kami siap membantu pertanyaan seputar produk, pesanan, pembayaran, dan pengiriman.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 2rem;">
                <strong>✅ {{ session('success') }}</strong>
                @if(session('wa_url'))
                    <div style="margin-top: 12px;">
                        <a href="{{ session('wa_url') }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                            <i class="fab fa-whatsapp"></i> Kirim via WhatsApp Sekarang
                        </a>
                    </div>
                @endif
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom: 2rem;">
                <strong>⚠️ Mohon periksa kembali:</strong>
                <ul style="margin-top: 8px; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="contact-page-grid">

            {{-- Form Kontak --}}
            <div class="info-page-card">
                <h2>Kirim Pesan</h2>
                <p style="color: var(--muted); margin-bottom: 24px; font-size: 14px;">
                    Isi form di bawah, pesan akan langsung kami terima via WhatsApp.
                </p>

                <form action="{{ route('pages.contact.send') }}" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span style="color: #e74c3c;">*</span></label>
                        <input
                            type="text"
                            name="name"
                            class="form-input {{ $errors->has('name') ? 'is-error' : '' }}"
                            placeholder="Masukkan nama lengkap Anda"
                            value="{{ old('name', auth()->user()->name ?? '') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span style="color: #e74c3c;">*</span></label>
                        <input
                            type="email"
                            name="email"
                            class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                            placeholder="contoh@email.com"
                            value="{{ old('email', auth()->user()->email ?? '') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subjek <span style="color: #e74c3c;">*</span></label>
                        <select name="subject" class="form-input {{ $errors->has('subject') ? 'is-error' : '' }}" required>
                            <option value="">-- Pilih Subjek --</option>
                            <option value="Pertanyaan Produk" {{ old('subject') === 'Pertanyaan Produk' ? 'selected' : '' }}>Pertanyaan Produk</option>
                            <option value="Status Pesanan" {{ old('subject') === 'Status Pesanan' ? 'selected' : '' }}>Status Pesanan</option>
                            <option value="Keluhan / Komplain" {{ old('subject') === 'Keluhan / Komplain' ? 'selected' : '' }}>Keluhan / Komplain</option>
                            <option value="Informasi Pembayaran" {{ old('subject') === 'Informasi Pembayaran' ? 'selected' : '' }}>Informasi Pembayaran</option>
                            <option value="Kerjasama" {{ old('subject') === 'Kerjasama' ? 'selected' : '' }}>Kerjasama</option>
                            <option value="Lainnya" {{ old('subject') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pesan <span style="color: #e74c3c;">*</span></label>
                        <textarea
                            name="message"
                            class="form-input {{ $errors->has('message') ? 'is-error' : '' }}"
                            rows="5"
                            placeholder="Tuliskan pesan atau pertanyaan Anda di sini..."
                            required
                            style="resize: vertical; min-height: 120px;"
                        >{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                    </button>
                </form>
            </div>

            {{-- Info Kontak --}}
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div class="info-page-card">
                    <h2>Informasi Kontak</h2>
                    <div class="contact-info-list">
                        <div>
                            <i class="fas fa-envelope"></i>
                            <span>Email</span>
                            <strong>rendiprano15@gmail.com</strong>
                        </div>
                        <div>
                            <i class="fas fa-phone"></i>
                            <span>Telepon / WhatsApp</span>
                            <strong>0821-2505-2233</strong>
                        </div>
                        <div>
                            <i class="fas fa-location-dot"></i>
                            <span>Alamat</span>
                            <strong>Pasar Rawa Kalong, Bekasi</strong>
                        </div>
                        <div>
                            <i class="fas fa-clock"></i>
                            <span>Jam Operasional</span>
                            <strong>Senin–Sabtu, 08.00–17.00 WIB</strong>
                        </div>
                    </div>
                </div>

                <div class="info-page-card">
                    <h2>Chat Langsung</h2>
                    <p style="color: var(--muted); font-size: 14px; margin-bottom: 16px;">
                        Untuk respons tercepat, hubungi kami langsung via WhatsApp.
                    </p>
                    <a
                        href="https://wa.me/6282125052233?text=Halo%20admin%20TOKO%20TIKA%2C%20saya%20ingin%20bertanya."
                        target="_blank"
                        rel="noopener"
                        class="btn btn-primary"
                        style="width: 100%; text-align: center;"
                    >
                        <i class="fab fa-whatsapp"></i> Chat Admin WhatsApp
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
