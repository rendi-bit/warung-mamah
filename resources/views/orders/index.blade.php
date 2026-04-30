@extends('layouts.store')

@section('content')
<section class="page-shell">
    <div class="container">
        <h1 class="page-title">Riwayat Pesanan</h1>
        <p class="page-lead">Pantau semua transaksi dan status pemesanan kamu secara real-time.</p>

        <div class="kpi-grid">
            <div class="kpi-card">
                <span class="kpi-label">Total Pesanan</span>
                <div class="kpi-value">{{ $orders->count() }}</div>
                <p class="kpi-help">Jumlah order yang tercatat pada akunmu.</p>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Pesanan Diproses</span>
                <div class="kpi-value">{{ $orders->where('order_status', 'processing')->count() }}</div>
                <p class="kpi-help">Order yang sedang dipersiapkan.</p>
            </div>
            <div class="kpi-card">
                <span class="kpi-label">Pesanan Selesai</span>
                <div class="kpi-value">{{ $orders->where('order_status', 'completed')->count() }}</div>
                <p class="kpi-help">Order yang sudah tuntas.</p>
            </div>
        </div>

        @if($orders->count())
            <table class="table-warung">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_code }}</td>
                        <td>Rp {{ number_format($order->grand_total,0,',','.') }}</td>
                        <td>{{ $order->order_status }}</td>
                        <td>
                            <a href="{{ route('orders.show', $order->id) }}" class="btn-warung">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h3>Belum Ada Pesanan</h3>
                <p>Pesanan kamu akan muncul di sini setelah checkout berhasil.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Mulai Belanja</a>
            </div>
        @endif
    </div>
</section>
@endsection