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

        {{-- Flash messages --}}
        @if(session('success'))
            <div style="padding:14px 20px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;color:#065f46;font-weight:500;">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="padding:14px 20px;background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;color:#991b1b;font-weight:500;">
                ❌ {{ session('error') }}
            </div>
        @endif

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
                <tr>
                    <td>
                        <strong>{{ $item->product->name ?? 'Produk tidak tersedia' }}</strong>
                        @if($item->variant)
                            <br><small>Varian: {{ $item->variant->variant_name }}</small>
                        @endif
                    </td>
                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ========================= --}}
        {{-- SECTION BUKTI PEMBAYARAN --}}
        {{-- ========================= --}}
        @if($order->payment_method === 'qris')
        <div class="card-dashboard">
            <h3 style="margin-bottom:16px;">📸 Bukti Pembayaran</h3>

            @if($order->payment_proof)
                <div style="margin-bottom:16px;">
                    <img
                        src="{{ asset('storage/' . $order->payment_proof) }}"
                        alt="Bukti Pembayaran"
                        style="max-width:320px; width:100%; border-radius:12px; border:1px solid #e5e7eb;"
                    >
                    @if($order->payment_status === 'paid')
                        <p style="margin-top:10px; color:#065f46; font-weight:500;">
                            ✅ Pembayaran telah dikonfirmasi admin.
                        </p>
                    @else
                        <p style="margin-top:10px; color:#92400e; font-weight:500;">
                            🕐 Menunggu konfirmasi admin.
                        </p>
                    @endif
                </div>
            @endif

            @if($order->payment_status !== 'paid')
            <form
                action="{{ route('orders.upload-proof', $order->id) }}"
                method="POST"
                enctype="multipart/form-data"
                id="uploadProofFormShow"
            >
                @csrf
                @method('PATCH')

                <div class="upload-proof-group">
                    <label class="upload-proof-label" for="payment_proof_show">
                        {{ $order->payment_proof ? '🔄 Ganti Bukti Pembayaran' : '⬆️ Upload Bukti Pembayaran' }}
                    </label>

                    <input
                        type="file"
                        name="payment_proof"
                        id="payment_proof_show"
                        accept="image/jpg,image/jpeg,image/png"
                        class="upload-proof-input @error('payment_proof') is-invalid @enderror"
                        onchange="previewImageShow(event)"
                    >

                    @error('payment_proof')
                        <p style="margin-top:6px;font-size:13px;color:#dc2626;font-weight:500;">⚠️ {{ $message }}</p>
                    @enderror

                    <p style="margin-top:4px;font-size:12px;color:#9ca3af;">Format: JPG, JPEG, PNG. Maks. 2MB.</p>

                    <div id="imagePreviewBoxShow" style="display:none; margin-top:12px;">
                        <p style="font-size:13px; color:#6b7280; margin-bottom:6px;">Preview:</p>
                        <img
                            id="imagePreviewShow"
                            src="#"
                            alt="Preview"
                            style="max-width:280px; max-height:200px; border-radius:10px; border:2px solid #e5e7eb; object-fit:contain;"
                        >
                    </div>
                </div>

                <button type="submit" class="btn-warung" id="uploadBtnShow" style="margin-top:12px;">
                    ⬆️ Upload Bukti Bayar
                </button>
            </form>
            @endif
        </div>
        @endif

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('orders.index') }}" class="btn-warung">Kembali ke Pesanan</a>
            @if($order->payment_method === 'qris' && $order->payment_status !== 'paid')
            <a href="{{ route('checkout.payment', $order->id) }}" class="btn-warung" style="background:#059669;">
                Ke Halaman Pembayaran
            </a>
            @endif
        </div>
    </div>
</section>

<script>
function previewImageShow(event) {
    const file = event.target.files[0];
    const previewBox = document.getElementById('imagePreviewBoxShow');
    const preview = document.getElementById('imagePreviewShow');

    if (!file) { previewBox.style.display = 'none'; return; }

    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    const maxSize = 2 * 1024 * 1024;

    if (!validTypes.includes(file.type)) {
        alert('Format file tidak valid. Gunakan JPG, JPEG, atau PNG.');
        event.target.value = '';
        previewBox.style.display = 'none';
        return;
    }

    if (file.size > maxSize) {
        alert('Ukuran file terlalu besar. Maksimal 2MB.');
        event.target.value = '';
        previewBox.style.display = 'none';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        previewBox.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

document.getElementById('uploadProofFormShow')?.addEventListener('submit', function() {
    const btn = document.getElementById('uploadBtnShow');
    btn.disabled = true;
    btn.textContent = '⏳ Mengupload...';
});
</script>
@endsection