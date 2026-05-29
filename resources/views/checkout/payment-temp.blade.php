@extends('layouts.store')

@section('content')

<section class="page-shell">

    <div class="container">

        <div class="payment-final-wrapper">

            {{-- HEADER --}}
            <div class="payment-final-header">

                <span class="payment-final-badge">
                    📸 Pembayaran QRIS
                </span>

                <h1>
                    Selesaikan Pembayaran
                </h1>

                <p>
                    Scan QRIS di bawah ini menggunakan aplikasi pembayaran favorit Anda.
                    Setelah pembayaran berhasil, unggah bukti pembayaran untuk diproses.
                </p>

            </div>

            {{-- MAIN CARD --}}
            <div class="payment-final-card">

                <div class="payment-final-grid">

                    {{-- LEFT --}}
                    <div class="payment-left-section">

                        <h3>
                            Scan QRIS
                        </h3>

                        <div class="payment-line"></div>

                        <p class="payment-description">
                            Screenshot, atau foto QRIS
                            untuk mempermudah pembayaran.
                        </p>

                        <div class="payment-qris-box">

                            <img
                                src="{{ asset('storage/avatars/qris.jpeg') }}"
                                alt="QRIS"
                                class="payment-qris-image"
                            >

                        </div>

                        <div class="payment-security-box">

                            <div class="payment-security-icon">
                                🛡️
                            </div>

                            <div>
                                <h4>Aman & Terpercaya</h4>
                                <p>
                                    Transaksi Anda aman menggunakan QRIS
                                    yang telah terstandar nasional.
                                </p>
                            </div>

                        </div>

                    </div>

                    {{-- RIGHT --}}
                    <div class="payment-right-section">

                        <h3>
                            Upload Bukti Pembayaran
                        </h3>

                        <div class="payment-line"></div>

                        <p class="payment-description">
                            Setelah melakukan pembayaran,
                            upload bukti pembayaran untuk verifikasi admin.
                        </p>

                        <div class="payment-alert-box">

                            <div class="payment-alert-icon">
                                ℹ️
                            </div>

                            <div>
                                <strong>Perhatian</strong>
                                <p>
                                    Pastikan bukti pembayaran jelas
                                    dan dapat terbaca dengan baik.
                                </p>
                            </div>

                        </div>

                        <form
                            action="{{ route('checkout.finalize') }}"
                            method="POST"
                            enctype="multipart/form-data"
                            id="paymentUploadForm"
                        >

                            @csrf

                            <div class="payment-upload-group">

                                <label>
                                    Pilih Bukti Pembayaran
                                </label>

                                <div class="payment-upload-box">

                                    <input
                                        type="file"
                                        name="payment_proof"
                                        id="payment_proof"
                                        accept="image/jpg,image/jpeg,image/png"
                                        required
                                    >

                                    <div class="payment-upload-content">

                                        <div class="payment-upload-icon">
                                            ☁️
                                        </div>

                                        <h4>
                                            Klik untuk memilih file
                                        </h4>

                                        <p>
                                            JPG, JPEG, PNG Maks. 2MB
                                        </p>

                                    </div>

                                </div>

                                @error('payment_proof')
                                    <small class="checkout-error">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            {{-- PREVIEW --}}
                            <div
                                id="imagePreviewWrapper"
                                class="payment-preview-wrapper"
                                style="display:none;"
                            >

                                <p class="payment-preview-title">
                                    Preview
                                </p>

                                <img
                                    id="imagePreview"
                                    src="#"
                                    alt="Preview"
                                >

                            </div>

                            <button
                                type="submit"
                                class="payment-submit-btn"
                                id="paymentSubmitBtn"
                            >
                                ⬆️ Upload Bukti & Buat Pesanan
                            </button>

                        </form>

                        <div class="payment-verification-box">

                            <div class="payment-verification-icon">
                                🔒
                            </div>

                            <div>
                                <h4>Proses Verifikasi</h4>
                                <p>
                                    Pesanan akan diproses setelah admin
                                    memverifikasi pembayaran Anda.
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

                {{-- FOOTER STEP --}}
                <div class="payment-step-wrapper">

                    <h3>
                        Cara Pembayaran
                    </h3>

                    <div class="payment-step-grid">

                        <div class="payment-step-item">

                            <div class="payment-step-icon">
                                📱
                            </div>

                            <h4>1. Buka Aplikasi</h4>

                            <p>
                                Buka aplikasi pembayaran seperti
                                GoPay, DANA, OVO, ShopeePay,
                                atau Mobile Banking.
                            </p>

                        </div>

                        <div class="payment-step-item">

                            <div class="payment-step-icon">
                                🖼️
                            </div>

                            <h4>2. Simpan QRIS</h4>

                            <p>
                                Screenshot, atau foto QRIS
                                untuk mempermudah pembayaran.
                            </p>

                        </div>

                        <div class="payment-step-item">

                            <div class="payment-step-icon">
                                💰
                            </div>

                            <h4>3. Masukkan Nominal</h4>

                            <p>
                                Pastikan nominal pembayaran sesuai
                                total checkout Anda.
                            </p>

                        </div>

                        <div class="payment-step-item">

                            <div class="payment-step-icon">
                                ✅
                            </div>

                            <h4>4. Upload Bukti</h4>

                            <p>
                                Upload bukti pembayaran untuk
                                proses verifikasi admin.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <div class="payment-footer-note">
                🔐 Informasi Anda aman dan tidak akan dibagikan kepada pihak lain.
            </div>

        </div>

    </div>

</section>

<script>

const paymentInput = document.getElementById('payment_proof');
const previewWrapper = document.getElementById('imagePreviewWrapper');
const previewImage = document.getElementById('imagePreview');

paymentInput?.addEventListener('change', function(e) {

    const file = e.target.files[0];

    if (!file) {
        previewWrapper.style.display = 'none';
        return;
    }

    const reader = new FileReader();

    reader.onload = function(event) {
        previewImage.src = event.target.result;
        previewWrapper.style.display = 'block';
    };

    reader.readAsDataURL(file);

});

document.getElementById('paymentUploadForm')
?.addEventListener('submit', function() {

    const btn = document.getElementById('paymentSubmitBtn');

    btn.disabled = true;
    btn.innerHTML = '⏳ Mengupload...';

});

</script>

@endsection