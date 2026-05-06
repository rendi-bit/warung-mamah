@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="info-page-hero">
            <span class="info-page-badge">Kontak Kami</span>
            <h1>Hubungi TOKO TIKA</h1>
            <p>Kami siap membantu pertanyaan seputar produk, pesanan, pembayaran, dan pengiriman.</p>
        </div>

        <div class="contact-page-grid">
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
                </div>
            </div>

            <div class="info-page-card">
                <h2>Chat WhatsApp</h2>
                <p>
                    Klik tombol di bawah untuk langsung menghubungi admin TOKO TIKA melalui WhatsApp.
                </p>

                <a
                    href="https://wa.me/6282125052233?text=Halo%20admin%20TOKO%20TIKA%2C%20saya%20ingin%20bertanya."
                    target="_blank"
                    rel="noopener"
                    class="btn btn-primary"
                >
                    Chat Admin WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>
@endsection