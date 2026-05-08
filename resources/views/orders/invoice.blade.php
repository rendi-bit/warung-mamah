@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="invoice-card">
            <div class="invoice-header">
                <div>
                    <span class="invoice-badge">Invoice</span>
                    <h1>Invoice Pesanan</h1>
                    <p>Kode Order: <strong>{{ $order->order_code }}</strong></p>
                </div>

                <a href="{{ route('orders.index') }}" class="btn btn-light">
                    Kembali
                </a>
            </div>

            <div class="invoice-info-grid">
                <div class="invoice-info-box">
                    <span>Pelanggan</span>
                    <strong>{{ $order->user->name ?? '-' }}</strong>
                    <p>{{ $order->user->email ?? '-' }}</p>
                </div>

                <div class="invoice-info-box">
                    <span>Metode Pembayaran</span>

                    @if($order->payment_method === 'qris')
                        <strong>QRIS</strong>
                    @else
                        <strong>COD / Bayar di Tempat</strong>
                    @endif
                </div>

                <div class="invoice-info-box">
                    <span>Status Pembayaran</span>

                    @if($order->payment_method === 'cod')
                        <strong>Bayar di Tempat</strong>
                    @else
                        <strong>{{ ucfirst($order->payment_status) }}</strong>
                    @endif
                </div>

                <div class="invoice-info-box">
                    <span>Tanggal Order</span>
                    <strong>{{ $order->created_at->format('d M Y H:i') }}</strong>
                </div>
            </div>

            @if($order->order_status === 'cancelled')
                <div class="invoice-cancel-note">
                    <strong>Pesanan Dibatalkan</strong>
                    <p>
                        Pesanan ini sudah dibatalkan.
                        @if($order->payment_method === 'qris' && $order->payment_status === 'paid')
                            Untuk refund pembayaran QRIS, silakan hubungi admin TOKO TIKA.
                        @endif
                    </p>
                </div>
            @endif

            <div class="invoice-table-wrap">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Varian</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? '-' }}</td>
                                <td>{{ $item->variant->variant_name ?? '-' }}</td>
                                <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td>{{ $item->quantity }} {{ $item->product->stock_unit ?? 'item' }}</td>
                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="invoice-summary">
                <div>
                    <span>Subtotal</span>
                    <strong>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong>
                </div>

                <div>
                    <span>Ongkir</span>
                    <strong>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</strong>
                </div>

                <div class="invoice-grand">
                    <span>Total</span>
                    <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection