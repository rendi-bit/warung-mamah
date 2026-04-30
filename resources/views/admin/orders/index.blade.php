@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">

        <div class="admin-page-header">
            <div class="admin-page-title">
                <h1>Kelola Pesanan</h1>
                <p>Pantau transaksi pelanggan, status pembayaran, dan proses pengiriman.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($orders->count())
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status Order</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->order_code }}</strong>
                                </td>

                                <td>
                                    <div class="admin-product-mini">
                                        <div class="admin-product-placeholder">👤</div>
                                        <div>
                                            <strong>{{ $order->user->name ?? 'Pelanggan' }}</strong>
                                            <span>{{ $order->user->email ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                                </td>

                                <td>
                                    <span class="admin-badge {{ $order->payment_status === 'paid' ? 'green' : 'yellow' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-badge blue">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $order->created_at ? $order->created_at->format('d M Y H:i') : '-' }}
                                </td>

                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-light btn-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="admin-empty">
                <div class="admin-empty-icon">🧾</div>
                <h3>Belum ada pesanan</h3>
                <p>Pesanan pelanggan akan muncul di halaman ini.</p>
            </div>
        @endif

    </div>
</section>
@endsection