@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">

        <div class="admin-page-header">
            <div class="admin-page-title">
                <h1>Detail Pesanan</h1>
                <p>Informasi lengkap pesanan dan item yang dibeli pelanggan.</p>
            </div>

            <a href="{{ route('admin.orders.index') }}" class="btn btn-light">
                Kembali
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="checkout-grid">
            <div class="admin-action-card">
                <h3>Informasi Pesanan</h3>

                <div class="checkout-items-list">
                    <div class="checkout-item-row">
                        <strong>Kode Pesanan</strong>
                        <span>{{ $order->order_code }}</span>
                    </div>

                    <div class="checkout-item-row">
                        <strong>Pelanggan</strong>
                        <span>{{ $order->user->name ?? '-' }}</span>
                    </div>

                    <div class="checkout-item-row">
                        <strong>Email</strong>
                        <span>{{ $order->user->email ?? '-' }}</span>
                    </div>

                    <div class="checkout-item-row">
                        <strong>Total</strong>
                        <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>

                    <div class="checkout-item-row">
                        <strong>Metode Pembayaran</strong>
                        <span>
                            @if($order->payment_method === 'qris')
                                <span class="admin-badge blue">QRIS</span>
                            @else
                                <span class="admin-badge orange">COD</span>
                            @endif
                        </span>
                    </div>

                    <div class="checkout-item-row">
                        <strong>Status Pembayaran</strong>
                        <span>
                            @if($order->payment_method === 'cod')
                                <span class="admin-badge orange">Bayar di Tempat</span>
                            @else
                                <span class="admin-badge {{ $order->payment_status === 'paid' ? 'green' : 'yellow' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="checkout-item-row">
                        <strong>Alamat</strong>
                        <span>{{ $order->shipping_address }}</span>
                    </div>
                </div>
            </div>

            @if($order->has_waiting_restock)
                <div class="admin-action-card restock-admin-alert">
                    <h3>Pesanan Menunggu Restok</h3>
                    <p>
                        Pesanan ini memiliki item yang jumlah pembeliannya melebihi stok tersedia.
                        Admin perlu menunggu stok masuk sebelum pesanan diproses penuh.
                    </p>

                    @if($order->restock_note)
                        <small>{{ $order->restock_note }}</small>
                    @endif

                    <form
                        action="{{ route('admin.orders.fulfillRestock', $order->id) }}"
                        method="POST"
                        style="margin-top: 16px;"
                        onsubmit="return confirm('Pastikan stok sudah tersedia. Tandai restok pesanan ini sebagai terpenuhi?')"
                    >
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="btn btn-primary">
                            Tandai Restok Terpenuhi
                        </button>
                    </form>
                </div>
            @endif

            <div class="admin-action-card">
                <h3>Update Status</h3>

                <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="form-warung">
                    @csrf
                    @method('PUT')

                    <label>Status Pembayaran</label>
                    <select name="payment_status" required>
                        <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>

                    <label>Status Pengiriman</label>
                    @if($order->order_status === 'completed')
                        <input type="hidden" name="order_status" value="completed">

                        <div class="status-readonly-box">
                            Pesanan sudah diselesaikan oleh user.
                        </div>
                    @else
                        <select name="order_status" required>
                            <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>

                            <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>
                                Shipping
                            </option>
                        </select>
                    @endif

                    <button type="submit" class="btn-warung">
                        Simpan Status
                    </button>
                </form>
            </div>
        </div>

        <div class="admin-action-card" style="margin-top: 24px;">
            <h3>Item Pesanan</h3>

            <div class="admin-table-wrap" style="margin-top: 16px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Varian</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Status Restok</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product->name ?? '-' }}</strong>

                                    @if($item->is_waiting_restock)
                                        <div class="item-restock-note">
                                            Kurang {{ $item->waiting_restock_quantity }}
                                            {{ $item->product->stock_unit ?? 'item' }}
                                            • Estimasi {{ $item->product->restock_estimation ?? '1 hari' }}
                                        </div>
                                    @endif
                                </td>

                                <td>{{ $item->variant->variant_name ?? '-' }}</td>

                                <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>

                                <td>
                                    {{ $item->quantity }} {{ $item->product->stock_unit ?? 'item' }}
                                </td>

                                <td>
                                    @if($item->is_waiting_restock)
                                        <span class="admin-badge orange">Menunggu Restok</span>
                                    @else
                                        <span class="admin-badge green">Stok Aman</span>
                                    @endif
                                </td>

                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>
@endsection