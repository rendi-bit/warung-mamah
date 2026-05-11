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

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($orders->count())
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Metode</th>
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
                                    @if($order->has_waiting_restock)
                                        <br>
                                        <span class="admin-badge orange" style="margin-top:4px; display:inline-flex; font-size:10px;">
                                            Restok
                                        </span>
                                    @endif
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
                                    @php
                                        $methodClass = $order->payment_method === 'qris' ? 'blue' : 'orange';
                                        $methodLabel = $order->payment_method === 'qris' ? 'QRIS' : 'COD';
                                    @endphp
                                    <span class="admin-badge {{ $methodClass }}">
                                        {{ $methodLabel }}
                                    </span>
                                </td>

                                <td>
                                    @php
                                        if ($order->payment_method === 'cod') {
                                            $paymentClass = 'orange';
                                            $paymentLabel = 'Bayar di Tempat';
                                        } else {
                                            $paymentClass = match($order->payment_status) {
                                                'paid'   => 'green',
                                                'failed' => 'red',
                                                default  => 'yellow',
                                            };
                                            $paymentLabel = match($order->payment_status) {
                                                'paid'   => 'Paid',
                                                'failed' => 'Failed',
                                                default  => 'Pending',
                                            };
                                        }
                                    @endphp
                                    <span class="admin-badge {{ $paymentClass }}">
                                        {{ $paymentLabel }}
                                    </span>
                                </td>

                                <td>
                                    @php
                                        $statusClass = match($order->order_status) {
                                            'completed' => 'green',
                                            'shipped'   => 'blue',
                                            'cancelled' => 'red',
                                            default     => 'yellow',
                                        };
                                        $statusLabel = match($order->order_status) {
                                            'pending'   => 'Pending',
                                            'shipped'   => 'Dikirim',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                            default     => ucfirst($order->order_status),
                                        };
                                    @endphp
                                    <span class="admin-badge {{ $statusClass }}">
                                        {{ $statusLabel }}
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

            {{-- ✅ FIX: Pagination untuk admin --}}
            @if($orders->hasPages())
                <div style="margin-top: 32px; display: flex; justify-content: center;">
                    {{ $orders->links() }}
                </div>
            @endif

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
