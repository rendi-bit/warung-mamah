@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">

        <div class="admin-page-header">
            <div class="admin-page-title">
                <h1>Detail Pesanan</h1>
                <p>Informasi lengkap pesanan dan item yang dibeli pelanggan.</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-light">Kembali</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
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
                        <strong>Kelurahan</strong>
                        <span>{{ $order->shippingArea->kelurahan ?? '-' }}</span>
                    </div>
                    <div class="checkout-item-row">
                        <strong>Alamat</strong>
                        <span>{{ $order->shipping_address }}</span>
                    </div>
                    <div class="checkout-item-row">
                        <strong>Ongkir</strong>
                        <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="checkout-item-row">
                        <strong>Metode Pengiriman</strong>
                        <span>
                            {{ $order->delivery_method === 'ojek_toko'
                                ? 'Ojek Toko'
                                : 'Ambil di Toko' }}
                        </span>
                    </div>
                </div>
            </div>

            @if($order->payment_proof)
                <div class="admin-action-card">
                    <h3>Bukti Pembayaran</h3>
                    <div style="margin-top:16px;">
                        <img
                            src="{{ asset('storage/' . $order->payment_proof) }}"
                            alt="Bukti Pembayaran"
                            style="width:100%;max-width:400px;border-radius:12px;border:1px solid #ddd;"
                        >
                    </div>
                    @if($order->payment_status !== 'paid')
                        <form action="{{ route('admin.orders.confirm-payment', $order->id) }}" method="POST" style="margin-top:20px;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
                        </form>
                    @endif
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
                        <option value="paid"    {{ $order->payment_status === 'paid'    ? 'selected' : '' }}>Paid</option>
                        <option value="failed"  {{ $order->payment_status === 'failed'  ? 'selected' : '' }}>Failed</option>
                    </select>

                    <label>Status Pengiriman</label>
                    @if($order->order_status === 'completed')
                        <input type="hidden" name="order_status" value="completed">
                        <div class="status-readonly-box">Pesanan sudah diselesaikan oleh user.</div>
                    @else
                        <select name="order_status" required>
                            <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipping</option>
                        </select>
                    @endif

                    <button type="submit" class="btn-warung">Simpan Status</button>
                </form>
            </div>
        </div>

        {{-- ✅ TABEL ITEM PESANAN + TOMBOL RESTOK PER BARIS --}}
        <div class="admin-action-card" style="margin-top:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="margin:0;">Item Pesanan</h3>

                {{-- Tombol restok semua sekaligus (jika masih ada yang waiting) --}}
                @if($order->has_waiting_restock)
                    <form
                        action="{{ route('admin.orders.fulfillRestock', $order->id) }}"
                        method="POST"
                        onsubmit="return confirm('Tandai semua item restok sebagai terpenuhi?')"
                    >
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-light" style="font-size:13px;">
                            ✅ Restok Semua
                        </button>
                    </form>
                @endif
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Varian</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Status Restok</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
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
                                <td>{{ $item->quantity }} {{ $item->product->stock_unit ?? 'item' }}</td>
                                <td>
                                    @if($item->is_waiting_restock)
                                        <span class="admin-badge orange">Menunggu Restok</span>
                                    @else
                                        <span class="admin-badge green">Stok Aman</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>

                                {{-- ✅ Tombol Restok per item --}}
                                <td>
                                    @if($item->is_waiting_restock)

                                        @php
                                            $message = "Restok {$item->waiting_restock_quantity} "
                                                . ($item->product->stock_unit ?? "item")
                                                . " untuk produk ini?";
                                        @endphp

                                        <form
                                            action="{{ route('admin.orders.restockItem', [$order->id, $item->id]) }}"
                                            method="POST"
                                            onsubmit="return confirm('{{ $message }}')"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn btn-primary"
                                                style="font-size:13px;padding:8px 14px;white-space:nowrap;">
                                                ✅ Restok
                                            </button>
                                        </form>

                                    @else
                                        <span style="color:#9ca3af;font-size:13px;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>
@endsection