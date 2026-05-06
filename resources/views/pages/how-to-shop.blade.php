@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="info-page-hero">
            <span class="info-page-badge">Cara Belanja</span>
            <h1>Cara Belanja di TOKO TIKA</h1>
            <p>Ikuti langkah mudah berikut untuk melakukan pemesanan.</p>
        </div>

        <div class="shopping-step-grid">
            <div class="shopping-step-card">
                <span>1</span>
                <h3>Pilih Produk</h3>
                <p>Buka halaman produk dan pilih barang yang ingin dibeli.</p>
            </div>

            <div class="shopping-step-card">
                <span>2</span>
                <h3>Masukkan Keranjang</h3>
                <p>Atur jumlah produk, lalu masukkan ke keranjang belanja.</p>
            </div>

            <div class="shopping-step-card">
                <span>3</span>
                <h3>Checkout</h3>
                <p>Isi alamat lengkap, nomor WhatsApp, dan pilih metode pengiriman.</p>
            </div>

            <div class="shopping-step-card">
                <span>4</span>
                <h3>Pilih Pembayaran</h3>
                <p>Gunakan QRIS atau COD sesuai kebutuhan kamu.</p>
            </div>

            <div class="shopping-step-card">
                <span>5</span>
                <h3>Pesanan Diproses</h3>
                <p>Admin akan mengecek pesanan dan memproses pengiriman.</p>
            </div>
        </div>
    </div>
</section>
@endsection