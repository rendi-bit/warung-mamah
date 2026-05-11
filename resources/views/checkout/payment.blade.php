@extends('layouts.store')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="max-width:700px;margin:16px auto;padding:14px 20px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;color:#065f46;font-weight:500;">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" style="max-width:700px;margin:16px auto;padding:14px 20px;background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;color:#991b1b;font-weight:500;">
        ❌ {{ session('error') }}
    </div>
@endif

<section class="section">
    <div class="container">
        <div class="qris-payment-wrap">
            <div class="qris-payment-card">
                <div class="qris-payment-header">
                    <span class="payment-badge">Pembayaran QRIS</span>
                    <h1>Selesaikan Pembayaran</h1>
                    <p>
                        Scan QRIS Toko Tika menggunakan GoPay, mobile banking, atau e-wallet yang mendukung QRIS.
                    </p>
                </div>

                <div class="qris-payment-grid">
                    <div class="qris-box">
                        <div class="qris-image-box">
                            <img src="{{ asset('storage/avatars/qris.jpeg') }}" alt="QRIS Toko Tika">
                        </div>

                        <p class="qris-scan-note">
                            Scan QRIS di atas, lalu bayar sesuai total pembayaran.
                        </p>
                    </div>

                    <div class="qris-order-info">
                        <div class="payment-order-item">
                            <span>Kode Order</span>
                            <strong>{{ $order->order_code }}</strong>
                        </div>

                        <div class="payment-order-item">
                            <span>Total Pembayaran</span>
                            <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
                        </div>

                        <div class="payment-order-item">
                            <span>Status Pembayaran</span>
                            <strong>{{ ucfirst($order->payment_status) }}</strong>
                        </div>

                        <div class="payment-order-item">
                            <span>Metode Pembayaran</span>
                            <strong>QRIS</strong>
                        </div>

                        <div class="qris-instruction-box">
                            <h3>Instruksi Pembayaran</h3>

                            <ol>
                                <li>Buka aplikasi GoPay, mobile banking, atau e-wallet kamu.</li>
                                <li>Pilih menu scan QRIS.</li>
                                <li>Scan kode QRIS Toko Tika.</li>
                                <li>Masukkan nominal sesuai total pembayaran.</li>
                                <li>Selesaikan pembayaran.</li>
                                <li>Upload bukti pembayaran di bawah ini.</li>
                            </ol>
                        </div>

                        {{-- ========================= --}}
                        {{-- FORM UPLOAD BUKTI BAYAR   --}}
                        {{-- ========================= --}}
                        @if($order->payment_status !== 'paid')
                        <form
                            action="{{ route('orders.upload-proof', $order->id) }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="upload-proof-form"
                            id="uploadProofForm"
                        >
                            @csrf
                            @method('PATCH')

                            <div class="upload-proof-group">
                                <label class="upload-proof-label" for="payment_proof">
                                    📸 Upload Bukti Pembayaran
                                </label>

                                <input
                                    type="file"
                                    name="payment_proof"
                                    id="payment_proof"
                                    accept="image/jpg,image/jpeg,image/png"
                                    class="upload-proof-input @error('payment_proof') is-invalid @enderror"
                                    onchange="previewImage(event)"
                                >

                                {{-- Pesan error validasi --}}
                                @error('payment_proof')
                                    <p class="upload-error-msg">⚠️ {{ $message }}</p>
                                @enderror

                                <p class="upload-hint">Format: JPG, JPEG, PNG. Maks. 2MB.</p>

                                {{-- Preview gambar sebelum upload --}}
                                <div id="imagePreviewBox" style="display:none; margin-top:12px;">
                                    <p style="font-size:13px; color:#6b7280; margin-bottom:6px;">Preview:</p>
                                    <img
                                        id="imagePreview"
                                        src="#"
                                        alt="Preview Bukti Pembayaran"
                                        style="
                                            max-width: 100%;
                                            max-height: 280px;
                                            border-radius: 10px;
                                            border: 2px solid #e5e7eb;
                                            object-fit: contain;
                                        "
                                    >
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-success payment-upload-btn"
                                id="uploadBtn"
                            >
                                ⬆️ Upload Bukti Bayar
                            </button>
                        </form>

                        @else
                        {{-- Tampilkan bukti yang sudah diupload --}}
                        <div class="upload-proof-group">
                            <label class="upload-proof-label">✅ Bukti Pembayaran Sudah Diupload</label>
                            @if($order->payment_proof)
                                <img
                                    src="{{ asset('storage/' . $order->payment_proof) }}"
                                    alt="Bukti Pembayaran"
                                    style="max-width:100%; max-height:280px; border-radius:10px; border:2px solid #6ee7b7; margin-top:10px;"
                                >
                                <p style="margin-top:8px; font-size:13px; color:#065f46;">
                                    ✅ Pembayaran sudah dikonfirmasi oleh admin.
                                </p>
                            @endif
                        </div>
                        @endif

                        {{-- Tampilkan bukti yang sudah diupload sebelumnya (jika belum paid) --}}
                        @if($order->payment_proof && $order->payment_status !== 'paid')
                        <div class="upload-proof-group" style="margin-top:16px;">
                            <label class="upload-proof-label" style="color:#6b7280;">
                                🕐 Bukti Sebelumnya (menunggu konfirmasi admin)
                            </label>
                            <img
                                src="{{ asset('storage/' . $order->payment_proof) }}"
                                alt="Bukti Pembayaran"
                                style="max-width:100%; max-height:200px; border-radius:10px; border:2px dashed #d1d5db; margin-top:8px; object-fit:contain;"
                            >
                        </div>
                        @endif

                        <div class="payment-action-box">
                            <a href="{{ route('orders.index') }}" class="btn btn-primary payment-main-btn">
                                Lihat Pesanan Saya
                            </a>

                            <a href="{{ route('cart.index') }}" class="btn btn-light payment-secondary-btn">
                                Kembali ke Keranjang
                            </a>
                        </div>
                    </div>
                </div>

                <div class="payment-note-box">
                    <div class="payment-note-item">
                        <span>📲</span>
                        <p>Pastikan nominal pembayaran sesuai total pesanan.</p>
                    </div>

                    <div class="payment-note-item">
                        <span>🧾</span>
                        <p>Status pembayaran akan diubah admin setelah pembayaran dicek.</p>
                    </div>

                    <div class="payment-note-item">
                        <span>🤎</span>
                        <p>Jika ada kendala, hubungi admin Toko Tika melalui WhatsApp.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.upload-error-msg {
    margin-top: 6px;
    font-size: 13px;
    color: #dc2626;
    font-weight: 500;
}
.upload-hint {
    margin-top: 4px;
    font-size: 12px;
    color: #9ca3af;
}
.upload-proof-input.is-invalid {
    border: 2px solid #ef4444 !important;
}
#uploadBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    const previewBox = document.getElementById('imagePreviewBox');
    const preview = document.getElementById('imagePreview');

    if (!file) {
        previewBox.style.display = 'none';
        return;
    }

    // Validasi client-side
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    const maxSize = 2 * 1024 * 1024; // 2MB

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

// Disable tombol saat submit agar tidak double-submit
document.getElementById('uploadProofForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('uploadBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Mengupload...';
});
</script>
@endsection