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

            @if($order->need_reupload)
                <div class="invoice-reupload-note">
                    <i class="fas fa-triangle-exclamation"></i>
                    <div>
                        <strong>Perlu Upload Ulang Bukti Bayar</strong>
                        <p>{{ $order->reupload_note ?? 'Bukti pembayaran kamu kurang jelas. Silakan upload ulang bukti pembayaran.' }}</p>
                    </div>
                </div>

                <div class="invoice-reupload-form">
                    <h3>Upload Bukti Pembayaran Baru</h3>

                    <form action="{{ route('orders.upload-proof', $order->id) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          id="reuploadForm">
                        @csrf
                        @method('PATCH')

                        <label class="invoice-upload-box" for="payment_proof">
                            <input type="file"
                                   name="payment_proof"
                                   id="payment_proof"
                                   accept="image/jpeg,image/jpg,image/png"
                                   required
                                   onchange="document.getElementById('proofFilename').textContent = this.files[0]?.name || ''; document.getElementById('proofFilename').classList.toggle('show', !!this.files[0]);">
                            <div class="invoice-upload-icon">
                                <i class="fas fa-cloud-arrow-up"></i>
                            </div>
                            <h4>Klik atau seret file di sini</h4>
                            <p>JPG, JPEG, atau PNG — maksimal 2MB</p>
                        </label>

                        <div id="proofFilename" class="invoice-upload-filename"></div>

                        @error('payment_proof')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                        <button type="submit" class="btn btn-primary">
                            Kirim Bukti Pembayaran
                        </button>
                    </form>
                </div>
            @elseif($order->payment_method === 'qris' && $order->payment_proof)
                <div class="invoice-proof-box">
                    <span>Bukti Pembayaran</span>
                    <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank">
                        <img src="{{ asset('storage/' . $order->payment_proof) }}" alt="Bukti Pembayaran">
                    </a>
                </div>
            @endif

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