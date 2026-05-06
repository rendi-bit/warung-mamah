@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="info-page-hero">
            <span class="info-page-badge">Tentang Kami</span>
            <h1>TOKO TIKA</h1>
            <p>
                TOKO TIKA adalah toko UMKM lokal yang menyediakan kebutuhan harian masyarakat
                dengan pelayanan yang ramah, cepat, dan terpercaya.
            </p>
        </div>

        <div class="info-page-card">
            <h2>Siapa Kami?</h2>
            <p>
                Kami hadir untuk membantu pelanggan mendapatkan produk kebutuhan rumah tangga,
                sembako, bumbu dapur, makanan ringan, dan produk pilihan lainnya dengan lebih mudah.
            </p>

            <p>
                Dengan sistem toko online ini, pelanggan dapat melihat produk, memasukkan keranjang,
                checkout, memilih pembayaran QRIS atau COD, dan memantau status pesanan secara online.
            </p>
        </div>

        <div class="info-feature-grid">
            <div class="info-feature-card">
                <i class="fas fa-store"></i>
                <h3>UMKM Lokal</h3>
                <p>Mendukung perkembangan toko lokal agar bisa melayani pelanggan secara modern.</p>
            </div>

            <div class="info-feature-card">
                <i class="fas fa-motorcycle"></i>
                <h3>Ojek Toko</h3>
                <p>Pengiriman dilakukan menggunakan ojek pribadi toko khusus area Bekasi Timur.</p>
            </div>

            <div class="info-feature-card">
                <i class="fas fa-qrcode"></i>
                <h3>QRIS & COD</h3>
                <p>Pembayaran mudah melalui QRIS atau bayar langsung saat pesanan diterima.</p>
            </div>
        </div>
    </div>
</section>
@endsection