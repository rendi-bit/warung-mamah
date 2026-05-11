@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="qris-payment-wrap">
            <div class="qris-payment-card">
                <div class="qris-payment-header">
                    <span class="payment-badge">Pembayaran QRIS</span>
                    <h1>Selesaikan Pembayaran</h1>
                    <p>
                        Scan QRIS Toko Tika menggunakan GoPay, mobile banking, atau e-wallet yang mendukung QRIS.
                    </p>
                </div>

                <div class="qris-payment-grid">
                    <div class="qris-box">
                        <div class="qris-image-box">
                            <img src="{{ asset('storage/avatars/qris.jpeg') }}" alt="QRIS Toko Tika">
                        </div>

                        <p class="qris-scan-note">
                            Scan QRIS di atas, lalu bayar sesuai total pembayaran.
                        </p>
                    </div>

                    <div class="qris-order-info">
                        <div class="payment-order-item">
                            <span>Kode Order</span>
                            <strong>{{ $order->order_code }}</strong>
                        </div>

                        <div class="payment-order-item">
                            <span>Total Pembayaran</span>
                            <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                        </div>

                        <div class="payment-order-item">
                            <span>Status Pembayaran</span>
                            <strong>{{ ucfirst($order->payment_status) }}</strong>
                        </div>

                        <div class="payment-order-item">
                            <span>Metode Pembayaran</span>
                            <strong>QRIS</strong>
                        </div>

                        <div class="qris-instruction-box">
                            <h3>Instruksi Pembayaran</h3>

                            <ol>
                                <li>Buka aplikasi GoPay, mobile banking, atau e-wallet kamu.</li>
                                <li>Pilih menu scan QRIS.</li>
                                <li>Scan kode QRIS Toko Tika.</li>
                                <li>Masukkan nominal sesuai total pembayaran.</li>
                                <li>Selesaikan pembayaran.</li>
                                <li>Admin akan mengecek pembayaran dan memproses pesanan.</li>
                            </ol>
                        </div>

                        <form
                            action="{{ route('orders.upload-proof', $order->id) }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="upload-proof-form"
                        >
                            @csrf
                            @method('PATCH')

                            <div class="upload-proof-group">

                                <label class="upload-proof-label">
                                    Upload Bukti Pembayaran
                                </label>

                                <input
                                    type="file"
                                    name="payment_proof"
                                    accept="image/*"
                                    required
                                    class="upload-proof-input"
                                >

                            </div>

                            <button
                                type="submit"
                                class="btn btn-success payment-upload-btn"
                            >
                                Upload Bukti Bayar
                            </button>
                        </form>

                        <div class="payment-action-box">
                            <a href="{{ route('orders.index') }}" class="btn btn-primary payment-main-btn">
                                Saya Sudah Bayar
                            </a>

                            <a href="{{ route('cart.index') }}" class="btn btn-light payment-secondary-btn">
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <div class="payment-note-box">
                    <div class="payment-note-item">
                        <span>📲</span>
                        <p>Pastikan nominal pembayaran sesuai total pesanan.</p>
                    </div>

                    <div class="payment-note-item">
                        <span>🧾</span>
                        <p>Status pembayaran akan diubah admin setelah pembayaran dicek.</p>
                    </div>

                    <div class="payment-note-item">
                        <span>🤎</span>
                        <p>Jika ada kendala, hubungi admin Toko Tika melalui WhatsApp.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection