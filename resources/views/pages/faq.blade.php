@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="info-page-hero">
            <span class="info-page-badge">FAQ</span>
            <h1>Pertanyaan yang Sering Ditanyakan</h1>
            <p>Temukan jawaban seputar belanja, pembayaran, dan pengiriman di TOKO TIKA.</p>
        </div>

        <div class="faq-list">
            <div class="faq-item">
                <h3>Apakah TOKO TIKA melayani pengiriman ke semua daerah?</h3>
                <p>Saat ini pengiriman difokuskan untuk area Bekasi Timur menggunakan ojek toko.</p>
            </div>

            <div class="faq-item">
                <h3>Metode pembayaran apa saja yang tersedia?</h3>
                <p>Kami menyediakan pembayaran QRIS dan COD atau bayar di tempat.</p>
            </div>

            <div class="faq-item">
                <h3>Apakah bisa ambil pesanan langsung di toko?</h3>
                <p>Bisa. Pilih metode pengiriman “Ambil di Toko” saat checkout.</p>
            </div>

            <div class="faq-item">
                <h3>Bagaimana cara mengetahui status pesanan?</h3>
                <p>Kamu bisa membuka menu Pesanan Saya untuk melihat status pembayaran dan pengiriman.</p>
            </div>
        </div>
    </div>
</section>
@endsection