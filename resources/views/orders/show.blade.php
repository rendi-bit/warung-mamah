@extends('layouts.store')

@section('content')
@php
    $statusClass = match (strtolower($order->order_status)) {
        'completed' => 'status-success',
        'processing' => 'status-info',
        'cancelled' => 'status-danger',
        default => 'status-warning',
    };
@endphp
<section class="page-shell">
    <div class="container stack">
        <div>
            <h1 class="page-title">Detail Pesanan</h1>
            <p class="page-lead">Ringkasan transaksi dan rincian item pembelian.</p>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <span class="kpi-label">Kode Pesanan</span>
                <div class="kpi-value">{{ $order->order_code }}</div>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Status</span>
                <div class="kpi-value">
                    <span class="order-status-badge {{ $statusClass }}">{{ strtoupper($order->order_status) }}</span>
                </div>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Total Pembayaran</span>
                <div class="kpi-value">Rp {{ number_format($order->grand_total,0,',','.') }}</div>
            </div>
        </div>

        <div class="card-dashboard">
            <p><strong>Alamat Pengiriman:</strong> {{ $order->shipping_address }}</p>
        </div>

        <table class="table-warung">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
    <div class="order-item-box">
        <p><strong>{{ $item->product->name }}</strong></p>

        @if($item->variant)
            <p>Varian: {{ $item->variant->variant_name }}</p>
        @endif

        <p>Jumlah: {{ $item->quantity }}</p>
        <p>Harga: Rp {{ number_format($item->price, 0, ',', '.') }}</p>
        <p>Subtotal: Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
    </div>
@endforeach
            </tbody>
        </table>

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('orders.index') }}" class="btn-warung">Kembali</a>
        </div>
    </div>
</section>
@endsection