@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">

        <div class="admin-page-header">
            <div class="admin-page-title">
                <h1>Riwayat Pesanan</h1>
                <p>Pantau semua transaksi, pembayaran, dan status pengiriman pesanan kamu.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="kpi-grid">
            <div class="kpi-card">
                <span class="kpi-label">Total Pesanan</span>
                <div class="kpi-value">{{ $totalOrders }}</div>
                <p class="kpi-help">Jumlah order yang tercatat pada akunmu.</p>
            </div>

            <div class="kpi-card">
                <span class="kpi-label">Pesanan Diproses</span>
                <div class="kpi-value">{{ $processingOrders }}</div>
                <p class="kpi-help">Order yang masih menunggu atau sedang dikirim.</p>
            </div>

            <div class="kpi-card">
                <span class="kpi-label">Pesanan Selesai</span>
                <div class="kpi-value">{{ $completedOrders }}</div>
                <p class="kpi-help">Order yang sudah selesai.</p>
            </div>
        </div>

        @if($orders->count())
            <div class="user-order-table-wrap">
                <table class="user-order-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Produk</th>
                            <th>Metode Pembayaran</th>
                            <th>Status Pembayaran</th>
                            <th>Status Pengiriman</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->order_code }}</strong>

                                    @if($order->has_waiting_restock)
                                        <span class="admin-badge orange" style="margin-top:6px; display:inline-flex;">
                                            Restok
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="order-products-list">
                                        @foreach($order->items as $item)
                                            <div class="order-product-mini">
                                                <div class="order-product-title-line">
                                                    <strong>{{ $item->product->name ?? '-' }}</strong>

                                                    @if($item->is_waiting_restock)
                                                        <span class="order-restock-badge">
                                                            Menunggu Restok
                                                        </span>
                                                    @endif
                                                </div>

                                                @if($item->variant)
                                                    <span>Varian: {{ $item->variant->variant_name }}</span>
                                                @endif

                                                <span>
                                                    Jumlah:
                                                    {{ $item->quantity }}
                                                    {{ $item->product->stock_unit ?? 'item' }}
                                                </span>

                                                <span>Harga: Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                                <span>Subtotal: Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>

                                                @if($item->is_waiting_restock)
                                                    <div class="order-restock-note">
                                                        <i class="fas fa-triangle-exclamation"></i>

                                                        <div>
                                                            <strong>Produk ini menunggu restok</strong>
                                                            <p>
                                                                Jumlah yang menunggu restok:
                                                                {{ $item->waiting_restock_quantity }}
                                                                {{ $item->product->stock_unit ?? 'item' }}.
                                                                Estimasi tersedia:
                                                                {{ $item->product->restock_estimation ?? '1 hari' }}.
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach

                                        <div class="order-grand-total">
                                            Total: Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                        </div>
                                    </div>
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
                                                'paid' => 'green',
                                                'failed' => 'red',
                                                default => 'yellow',
                                            };

                                            $paymentLabel = match($order->payment_status) {
                                                'paid' => 'Paid',
                                                'failed' => 'Failed',
                                                default => 'Pending',
                                            };
                                        }
                                    @endphp

                                    <span class="admin-badge {{ $paymentClass }}">
                                        {{ $paymentLabel }}
                                    </span>
                                </td>

                                <td>
                                    @php
                                        $shippingLabel = match($order->order_status) {
                                            'pending' => 'Pending',
                                            'shipped' => 'Shipping',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan',
                                            default => ucfirst($order->order_status),
                                        };

                                        $shippingClass = match($order->order_status) {
                                            'completed' => 'green',
                                            'shipped' => 'blue',
                                            'cancelled' => 'red',
                                            default => 'yellow',
                                        };
                                    @endphp

                                    <span class="admin-badge {{ $shippingClass }}">
                                        {{ $shippingLabel }}
                                    </span>

                                    @if($order->has_waiting_restock)
                                        <div class="order-status-help">
                                            Ada produk yang sedang menunggu restok. Pesanan akan diproses setelah stok tersedia.
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div class="order-action-stack">
                                        <a href="{{ route('orders.invoice', $order->id) }}" class="btn btn-light btn-sm">
                                            Invoice
                                        </a>

                                        @if($order->order_status === 'completed')
                                            <span class="admin-badge green">
                                                Pesanan Selesai
                                            </span>

                                        @elseif($order->order_status === 'shipped')
                                            <form action="{{ route('orders.complete', $order->id) }}" method="POST" onsubmit="return confirm('Yakin pesanan ini sudah kamu terima dan ingin diselesaikan?')">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    Selesaikan Pesanan
                                                </button>
                                            </form>

                                        @elseif($order->order_status === 'cancelled')
                                            <span class="admin-badge red">
                                                Pesanan Dibatalkan
                                            </span>

                                            @if($order->payment_method === 'qris' && $order->payment_status === 'paid')
                                                <small class="refund-help">
                                                    Hubungi admin untuk proses refund QRIS.
                                                </small>
                                            @endif

                                        @else
                                            @if($order->created_at->gte(now()->subDay()))
                                                <form action="{{ route('orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Stok produk akan dikembalikan.')">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit" class="btn btn-light btn-sm">
                                                        Batalkan Pesanan
                                                    </button>
                                                </form>
                                            @else
                                                <span class="admin-badge yellow">
                                                    Batas Cancel Habis
                                                </span>
                                            @endif

                                            <span class="admin-badge yellow">
                                                Menunggu Shipping
                                            </span>
                                        @endif
                                    </div>
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
                <p>Pesanan kamu akan muncul di halaman ini setelah checkout.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Belanja Sekarang</a>
            </div>
        @endif

    </div>
</section>
@endsection