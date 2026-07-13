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


        {{-- Status Pesanan --}}
        @if($order->order_status === 'cancelled')

        <div class="order-status-box cancelled">
            <div class="status-icon">✖</div>

            <div>
                <h3>Dibatalkan oleh Pelanggan</h3>
                <p>
                    Pesanan ini telah dibatalkan oleh pelanggan sehingga
                    tidak dapat diproses ataupun dikirim.
                </p>
            </div>
        </div>

        @elseif($order->order_status === 'pending')

        <div class="order-status-box pending">
            <div class="status-icon">⏳</div>

            <div>
                <h3>Menunggu Konfirmasi</h3>
                <p>
                    Pesanan sedang menunggu konfirmasi dari admin.
                </p>
            </div>
        </div>

        @elseif($order->order_status === 'processed')

        <div class="order-status-box processed">
            <div class="status-icon">📦</div>

            <div>
                <h3>Sedang Diproses</h3>
                <p>
                    Pesanan sedang dipersiapkan oleh admin.
                </p>
            </div>
        </div>

        @elseif($order->order_status === 'shipped')

        <div class="order-status-box shipped">
            <div class="status-icon">🚚</div>

            <div>
                <h3>Sedang Dikirim</h3>
                <p>
                    Pesanan sedang dalam perjalanan menuju pelanggan.
                </p>
            </div>
        </div>

        @elseif($order->order_status === 'completed')

        <div class="order-status-box completed">
            <div class="status-icon">✔</div>

            <div>
                <h3>Pesanan Selesai</h3>
                <p>
                    Pesanan telah diterima pelanggan.
                </p>
            </div>
        </div>

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

                <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap;">

                    <form action="{{ route('admin.orders.confirm-payment', $order->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="btn btn-primary">
                            ✔ Konfirmasi Pembayaran
                        </button>
                    </form>

                    <form action="{{ route('admin.orders.request-reupload', $order->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="btn btn-light"
                            onclick="return confirm('Minta pelanggan upload ulang bukti pembayaran?')">
                            📷 Foto Kurang Jelas
                        </button>
                    </form>

                </div>

                @endif

            </div>

            @endif
            <div class="admin-action-card">
                <h3>Update Status</h3>

                @if($order->order_status !== 'cancelled')

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
                                <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipping</option>
                            </select>
                        @endif

                        <button type="submit" class="btn-warung">
                            Simpan Status
                        </button>

                    </form>

                @else

                    <div class="order-status-box cancelled" style="margin-top:15px;">
                        <div class="status-icon">✖</div>

                        <div>
                            <h3>Pesanan Sudah Dibatalkan</h3>
                            <p>
                                Pesanan ini telah dibatalkan oleh pelanggan sehingga
                                status tidak dapat diubah lagi.
                            </p>
                        </div>
                    </div>
                @endif
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