@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="payment-page-wrap">
            <div class="payment-summary-card">
                <div class="payment-summary-header">
                    <span class="payment-badge">Pembayaran Midtrans</span>
                    <h1>Selesaikan Pembayaran Pesanan</h1>
                    <p>Silakan cek detail pesanan kamu sebelum melanjutkan pembayaran.</p>
                </div>

                <div class="payment-order-grid">
                    <div class="payment-order-item">
                        <span>Kode Order</span>
                        <strong>{{ $order->order_code }}</strong>
                    </div>

                    <div class="payment-order-item">
                        <span>Status Pembayaran</span>
                        <strong>{{ ucfirst($order->payment_status) }}</strong>
                    </div>

                    <div class="payment-order-item">
                        <span>Status Pesanan</span>
                        <strong>{{ ucfirst($order->order_status) }}</strong>
                    </div>

                    <div class="payment-order-item">
                        <span>Total Pembayaran</span>
                        <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="payment-address-box">
                    <h3>Alamat Pengiriman</h3>
                    <p>{{ $order->shipping_address }}</p>
                </div>

                <div class="payment-action-box">
                    <button id="pay-button" class="btn btn-primary payment-main-btn">
                        Bayar Sekarang
                    </button>

                    <a href="{{ route('orders.index') }}" class="btn btn-light payment-secondary-btn">
                        Lihat Pesanan Saya
                    </a>
                </div>

                <div class="payment-note-box">
                    <div class="payment-note-item">
                        <span>🔒</span>
                        <p>Pembayaran diproses aman melalui Midtrans.</p>
                    </div>
                    <div class="payment-note-item">
                        <span>📲</span>
                        <p>Pilih metode pembayaran yang tersedia di halaman Midtrans.</p>
                    </div>
                    <div class="payment-note-item">
                        <span>🧾</span>
                        <p>Status order akan diperbarui setelah pembayaran diproses.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
document.getElementById('pay-button').addEventListener('click', function () {
    window.snap.pay('{{ $snapToken }}', {
        onSuccess: function(result) {
            window.location.href = '{{ route("orders.index") }}';
        },
        onPending: function(result) {
            window.location.href = '{{ route("orders.index") }}';
        },
        onError: function(result) {
            alert('Pembayaran gagal. Silakan coba lagi.');
        },
        onClose: function() {
            alert('Kamu menutup popup pembayaran sebelum menyelesaikan transaksi.');
        }
    });
});
</script>
@endsection