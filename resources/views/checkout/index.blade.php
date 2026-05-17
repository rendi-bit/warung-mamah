@extends('layouts.store')

@section('content')
<section class="page-shell">
    <div class="container">
        <div class="checkout-page-header">
            <span class="checkout-badge">Checkout Bekasi Timur</span>
            <h1>Lengkapi Pesanan</h1>
            <p>
                Toko Tika saat ini melayani pengiriman khusus area Bekasi Timur menggunakan ojek toko,
                atau pelanggan bisa mengambil langsung di toko.
            </p>
        </div>

        @php
            $subtotal = 0;
            foreach($cart->items as $item) {
                $price = $item->variant ? $item->variant->price : $item->product->price;
                $subtotal += $price * $item->quantity;
            }
            $ojekCost   = 10000;
            $pickupCost = 0;
        @endphp

        {{-- Flash messages --}}
        @if(session('error'))
            <div style="padding:14px 20px;background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;color:#991b1b;font-weight:500;margin-bottom:16px;">
                ❌ {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST" class="checkout-modern-form">
            @csrf

            <div class="checkout-modern-grid">

                {{-- ============================= --}}
                {{-- STEP 1: INFORMASI PENERIMA   --}}
                {{-- ============================= --}}
                <div class="checkout-form-card">
                    <div class="checkout-card-title">
                        <span>1</span>
                        <div>
                            <h3>Informasi Penerima</h3>
                            <p>Isi data penerima agar pesanan mudah dikonfirmasi.</p>
                        </div>
                    </div>

                    <div class="checkout-field">
                        <label>Nomor WhatsApp</label>
                        <input
                            type="text"
                            name="customer_whatsapp"
                            value="{{ old('customer_whatsapp', auth()->user()->phone ?? $lastOrder->customer_whatsapp ?? '') }}"
                            placeholder="Contoh: 082125052233"
                            required
                        >
                        @error('customer_whatsapp')
                            <small class="checkout-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="checkout-field">
                        <label>Alamat Lengkap</label>
                        <textarea
                            name="shipping_address"
                            rows="5"
                            placeholder="Contoh: Jl. Mawar No. 12, RT 03/RW 01, Bekasi Timur"
                            required
                        >{{ old('shipping_address', auth()->user()->address ?? $lastOrder->shipping_address ?? '') }}</textarea>
                        @error('shipping_address')
                            <small class="checkout-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="checkout-field">
                        <label>Patokan Rumah <small style="color:#9ca3af;">(opsional)</small></label>
                        <input
                            type="text"
                            name="house_landmark"
                            value="{{ old('house_landmark', auth()->user()->house_landmark ?? $lastOrder->house_landmark ?? '') }}"
                            placeholder="Contoh: dekat masjid, pagar hitam, samping warung"
                        >
                        @error('house_landmark')
                            <small class="checkout-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="checkout-field">
                        <label>Catatan Pesanan <small style="color:#9ca3af;">(opsional)</small></label>
                        <textarea
                            name="notes"
                            rows="3"
                            placeholder="Contoh: tolong antar sore, hubungi dulu sebelum sampai"
                        >{{ old('notes', $lastOrder->notes ?? '') }}</textarea>
                        @error('notes')
                            <small class="checkout-error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                {{-- ============================= --}}
                {{-- STEP 2: METODE PENGIRIMAN    --}}
                {{-- ============================= --}}
                <div class="checkout-form-card">
                    <div class="checkout-card-title">
                        <span>2</span>
                        <div>
                            <h3>Metode Pengiriman</h3>
                            <p>Pilih metode pengiriman yang tersedia untuk area Bekasi Timur.</p>
                        </div>
                    </div>

                    <div class="delivery-options">
                        <label class="delivery-option-card active" data-cost="{{ $ojekCost }}">
                            <input
                                type="radio"
                                name="delivery_method"
                                value="ojek_toko"
                                data-cost="{{ $ojekCost }}"
                                checked
                            >
                            <div class="delivery-icon">
                                <i class="fas fa-motorcycle"></i>
                            </div>
                            <div>
                                <strong>Pengiriman Ojek Toko</strong>
                                <p>Dikirim oleh kurir/ojek pribadi toko khusus area Bekasi Timur.</p>
                                <span>Rp {{ number_format($ojekCost, 0, ',', '.') }}</span>
                            </div>
                        </label>

                        <label class="delivery-option-card" data-cost="{{ $pickupCost }}">
                            <input
                                type="radio"
                                name="delivery_method"
                                value="ambil_di_toko"
                                data-cost="{{ $pickupCost }}"
                            >
                            <div class="delivery-icon">
                                <i class="fas fa-store"></i>
                            </div>
                            <div>
                                <strong>Ambil di Toko</strong>
                                <p>Pesanan diambil langsung di toko setelah dikonfirmasi admin.</p>
                                <span>Gratis</span>
                            </div>
                        </label>
                    </div>

                    <div class="checkout-area-note">
                        <i class="fas fa-location-dot"></i>
                        <p>
                            Pengiriman hanya untuk area Bekasi Timur. Jika alamat di luar area,
                            admin akan menghubungi melalui WhatsApp.
                        </p>
                    </div>
                </div>

                {{-- ============================= --}}
                {{-- STEP 3: RINGKASAN + BAYAR    --}}
                {{-- ✅ FIX: Step 3 dan 4 digabung, nomor tidak duplikat --}}
                {{-- ============================= --}}
                <aside class="checkout-summary-card">
                    <div class="checkout-card-title">
                        <span>3</span>
                        <div>
                            <h3>Ringkasan & Pembayaran</h3>
                            <p>Cek produk, total, dan pilih metode pembayaran.</p>
                        </div>
                    </div>

                    <div class="checkout-items-modern">
                        @foreach($cart->items as $item)
                            @php
                                $price        = $item->variant ? $item->variant->price : $item->product->price;
                                $itemSubtotal = $price * $item->quantity;
                            @endphp

                            <div class="checkout-product-mini">
                                <div class="checkout-product-image">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}">
                                    @else
                                        <i class="fas fa-box-open"></i>
                                    @endif
                                </div>

                                <div class="checkout-product-info">
                                    <strong>{{ $item->product->name ?? 'Produk' }}</strong>
                                    @if($item->variant)
                                        <span>{{ $item->variant->variant_name }}</span>
                                    @endif
                                    @if($item->is_waiting_restock)
                                        <span style="font-size:11px;color:#d97706;">⏳ Menunggu restok</span>
                                    @endif
                                    <small>{{ $item->quantity }} x Rp {{ number_format($price, 0, ',', '.') }}</small>
                                </div>

                                <div class="checkout-product-price">
                                    Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="checkout-summary-lines">
                        <div>
                            <span>Subtotal Produk</span>
                            <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span>Ongkir</span>
                            <strong id="shippingCostText">Rp {{ number_format($ojekCost, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="checkout-grand-total">
                        <span>Total Pembayaran</span>
                        <strong id="grandTotalText">
                            Rp {{ number_format($subtotal + $ojekCost, 0, ',', '.') }}
                        </strong>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div style="margin-top:20px;">
                        <p style="font-weight:600;margin-bottom:10px;">Metode Pembayaran</p>

                        <div class="payment-method-options">
                            <label class="payment-method-card active">
                                <input type="radio" name="payment_method" value="qris" checked>
                                <div class="payment-method-icon">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <div>
                                    <strong>QRIS</strong>
                                    <p>Bayar dengan scan QRIS melalui GoPay, mobile banking, atau e-wallet lain.</p>
                                </div>
                            </label>

                            <label class="payment-method-card">
                                <input type="radio" name="payment_method" value="cod">
                                <div class="payment-method-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div>
                                    <strong>COD / Bayar di Tempat</strong>
                                    <p>Bayar saat pesanan sampai atau saat mengambil di toko.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <input type="hidden" id="subtotalValue" value="{{ $subtotal }}">

                    <div class="checkout-actions-modern">
                        <a href="{{ route('cart.index') }}" class="btn btn-light">
                            Kembali ke Keranjang
                        </a>
                        <button type="submit" class="btn btn-primary checkout-submit-btn" id="checkoutBtn">
                            Proses Pesanan
                        </button>
                    </div>
                </aside>

            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deliveryCards  = document.querySelectorAll('.delivery-option-card');
    const deliveryInputs = document.querySelectorAll('input[name="delivery_method"]');
    const shippingText   = document.getElementById('shippingCostText');
    const grandTotalText = document.getElementById('grandTotalText');
    const subtotalValue  = document.getElementById('subtotalValue');
    const checkoutBtn    = document.getElementById('checkoutBtn');

    const formatRupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

    const updateTotal = function () {
        const selected     = document.querySelector('input[name="delivery_method"]:checked');
        const shippingCost = selected ? parseInt(selected.dataset.cost) : 0;
        const subtotal     = parseInt(subtotalValue.value) || 0;

        shippingText.textContent   = shippingCost === 0 ? 'Gratis' : formatRupiah(shippingCost);
        grandTotalText.textContent = formatRupiah(subtotal + shippingCost);

        deliveryCards.forEach(c => c.classList.remove('active'));
        if (selected) selected.closest('.delivery-option-card').classList.add('active');
    };

    deliveryInputs.forEach(i => i.addEventListener('change', updateTotal));

    // ✅ Disable tombol submit agar tidak double-submit
    document.querySelector('.checkout-modern-form').addEventListener('submit', function () {
        checkoutBtn.disabled    = true;
        checkoutBtn.textContent = '⏳ Memproses...';
    });

    updateTotal();
});
</script>
@endsection