@extends('layouts.store')

@section('content')
<section class="page-shell">
    <div class="container">
        
        <!-- Hero Section -->
        <div class="checkout-hero">
            <div class="checkout-hero-left">
                <span class="checkout-badge">📍 Khusus Bekasi Timur</span>
                <h1>Lengkapi Pesanan Anda</h1>
                <p>
                    Belanja kebutuhan dapur menjadi lebih mudah bersama Toko Tika.
                    Kami melayani pengiriman khusus wilayah Bekasi Timur menggunakan
                    ojek toko atau pesanan dapat diambil langsung di toko.
                </p>
                <div class="checkout-feature-grid">
                    <div class="checkout-feature-card">
                        <div class="feature-icon">🚚</div>
                        <h4>Ojek Toko</h4>
                        <span>Pengiriman Cepat</span>
                    </div>
                    <div class="checkout-feature-card">
                        <div class="feature-icon">🏪</div>
                        <h4>Ambil</h4>
                        <span>Di Toko</span>
                    </div>
                    <div class="checkout-feature-card">
                        <div class="feature-icon">💳</div>
                        <h4>QRIS</h4>
                        <span>COD Tersedia</span>
                    </div>
                    <div class="checkout-feature-card">
                        <div class="feature-icon">📱</div>
                        <h4>WhatsApp</h4>
                        <span>Konfirmasi Admin</span>
                    </div>
                </div>
            </div>
            <div class="checkout-hero-right">
                <img src="{{ asset('storage/avatars/checkout-delivery.jpg') }}" alt="Checkout">
            </div>
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

            <div class="checkout-wrapper">

                <!-- Left Column (Separate Steps just like the mockup) -->
                <div class="checkout-left">

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

                        <div class="checkout-form-grid">
                            
                            <!-- WhatsApp Field -->
                            <div class="checkout-field-group">
                                <div class="checkout-field-icon">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div class="checkout-field-main">
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
                            </div>

                            <!-- Alamat Lengkap Field -->
                            <div class="checkout-field-group">
                                <div class="checkout-field-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="checkout-field-main">
                                    <label>Alamat Lengkap</label>
                                    <textarea
                                        name="shipping_address"
                                        rows="2"
                                        placeholder="Contoh: Jl. Mawar No. 12, RT 03/RW 01, Bekasi Timur"
                                        required
                                    >{{ old('shipping_address', auth()->user()->address ?? $lastOrder->shipping_address ?? '') }}</textarea>
                                    @error('shipping_address')
                                        <small class="checkout-error">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- Kelurahan Field -->
                            <div class="checkout-field-group">
                                <div class="checkout-field-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="checkout-field-main">
                                    <label>Kelurahan</label>
                                    <select name="shipping_area" id="shipping_area" required>
                                        <option value="">Pilih Kelurahan</option>
                                        @foreach($shippingAreas as $area)
                                            <option
                                                value="{{ $area->id }}"
                                                data-cost="{{ $area->shipping_cost }}"
                                                {{ (old('shipping_area', $lastOrder->shipping_area_id ?? '') == $area->id) ? 'selected' : '' }}>
                                                {{ $area->kelurahan }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shipping_area')
                                        <small class="checkout-error">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- Catatan Pesanan Field -->
                            <div class="checkout-field-group">
                                <div class="checkout-field-icon">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <div class="checkout-field-main">
                                    <label>Catatan Pesanan</label>
                                    <textarea
                                        name="notes"
                                        rows="2"
                                        maxlength="200"
                                        id="checkout_notes"
                                        placeholder="Contoh: tolong antar sore, hubungi dulu sebelum sampai"
                                    >{{ old('notes', $lastOrder->notes ?? '') }}</textarea>
                                    <span class="checkout-char-counter" id="char_counter">0 / 200</span>
                                </div>
                            </div>

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
                            <!-- Ojek Toko Option -->
                            <label class="delivery-option-card active" id="delivery_ojek_label" data-cost="{{ $ojekCost }}">
                                <input type="radio" name="delivery_method" value="ojek_toko" data-cost="{{ $ojekCost }}" checked>
                                <span class="option-radio-circle"></span>
                                <div class="delivery-icon">
                                    <i class="fas fa-motorcycle"></i>
                                </div>
                                <div style="flex:1;">
                                    <strong>Pengiriman Ojek Toko <span class="delivery-badge">Rekomendasi</span></strong>
                                    <p>Dikirim oleh kurir pribadi Toko Tika khusus wilayah Bekasi Timur.</p>
                                    <div class="delivery-stats">
                                        <div><span>Estimasi</span><span>30 – 60 menit</span></div>
                                        <div><span>Ongkir</span><span id="deliveryCostInfo">Pilih kelurahan</span></div>
                                    </div>
                                </div>
                            </label>

                            <!-- Ambil di Toko Option -->
                            <label class="delivery-option-card" id="delivery_pickup_label" data-cost="{{ $pickupCost }}">
                                <input type="radio" name="delivery_method" value="ambil_di_toko" data-cost="{{ $pickupCost }}">
                                <span class="option-radio-circle"></span>
                                <div class="delivery-icon">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div style="flex:1;">
                                    <strong>Ambil di Toko</strong>
                                    <p>Pesanan diambil langsung di toko setelah dikonfirmasi admin.</p>
                                    <div class="delivery-stats">
                                        <div><span>Estimasi</span><span>Hari ini</span></div>
                                        <div><span>Ongkir</span><span>Gratis</span></div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- ============================= --}}
                    {{-- STEP 3: METODE PEMBAYARAN    --}}
                    {{-- ============================= --}}
                    <div class="checkout-form-card">
                        <div class="checkout-card-title">
                            <span>3</span>
                            <div>
                                <h3>Metode Pembayaran</h3>
                                <p>Pilih metode pembayaran yang ingin digunakan.</p>
                            </div>
                        </div>

                        <div class="payment-method-options">
                            <!-- QRIS Option -->
                            <label class="payment-method-card active" id="payment_qris_label">
                                <input type="radio" name="payment_method" value="qris" checked>
                                <span class="option-radio-circle"></span>
                                <div class="payment-method-icon">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <div>
                                    <strong>QRIS</strong>
                                    <p>Bayar menggunakan QRIS melalui GoPay, Mobile Banking, atau e-wallet.</p>
                                </div>
                            </label>

                            <!-- COD Option -->
                            <label class="payment-method-card" id="payment_cod_label">
                                <input type="radio" name="payment_method" value="cod">
                                <span class="option-radio-circle"></span>
                                <div class="payment-method-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div>
                                    <strong>COD (Bayar di Tempat)</strong>
                                    <p>Bayar tunai saat pesanan diterima.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Right Column (Exactly matching the mockup structure) -->
                <div class="checkout-right">
                    
                    <!-- White Card Wrapper ONLY for Product Summary -->
                    <aside class="checkout-summary-card">
                        <div class="checkout-card-title" style="margin-bottom: 20px;">
                            <div class="payment-method-icon" style="background:#fdf8f2; font-size: 20px;">
                                <i class="fas fa-receipt" style="color: #ea580c;"></i>
                            </div>
                            <div style="flex:1; display:flex; justify-content:space-between; align-items:center;">
                                <h3 style="font-size:18px; margin:0;">Ringkasan Pesanan</h3>
                                <span style="font-size:13px; color:#8c7a6b; font-weight:700;">{{ $cart->items->count() }} Produk</span>
                            </div>
                        </div>

                        <!-- Product List -->
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
                                        @if(isset($item->is_waiting_restock) && $item->is_waiting_restock)
                                            <span class="cart-restock-pill" style="margin-top:2px;">⏳ Menunggu restok</span>
                                        @endif
                                        <small>{{ $item->quantity }} x Rp {{ number_format($price, 0, ',', '.') }}</small>
                                    </div>
                                    <div class="checkout-product-price">
                                        Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Spacing Details -->
                        <div class="checkout-summary-lines">
                            <div>
                                <span>Subtotal Produk</span>
                                <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span>Ongkir</span>
                                <strong id="shippingCostText">Pilih Kelurahan</strong>
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div class="checkout-grand-total">
                            <span>Total Pembayaran</span>
                            <strong id="grandTotalText">
                                Rp {{ number_format($subtotal, 0, ',', '.') }}
                            </strong>
                        </div>

                        <input type="hidden" id="subtotalValue" value="{{ $subtotal }}">
                    </aside>

                    <!-- Safe Alert Info Banner (Placed outside card on the page background) -->
                    <div class="checkout-area-note" style="margin: 20px 0; background:#fff7ed; border-color:#fed7aa; color:#9a3412;">
                        <i class="fas fa-location-dot" style="margin-top: 3px;"></i>
                        <p style="font-size:13px; margin:0;">
                            Pengiriman hanya untuk area Bekasi Timur. Jika alamat di luar area,
                            admin akan menghubungi melalui WhatsApp.
                        </p>
                    </div>

                    <!-- Action Submit Button (Placed outside card on the page background) -->
                    <button
                        type="submit"
                        class="btn btn-primary checkout-submit-btn"
                        id="checkoutBtn"
                        style="background: #9a5315; border:none; border-radius: 18px; color:white; font-weight:800; font-size:16px; display:flex; align-items:center; justify-content:center; gap:8px; width: 100%;">
                        <i class="fas fa-lock"></i> Lanjut ke Pembayaran
                    </button>

                    <!-- Trust Badges (Placed outside card on the page background) -->
                    <div class="checkout-safe-note row-variant" style="margin-top: 20px;">
                        <div><i class="fas fa-shield-alt"></i><span>Transaksi Aman</span></div>
                        <div><i class="fas fa-check-circle"></i><span>Produk Berkualitas</span></div>
                        <div><i class="fas fa-star"></i><span>Layanan Terpercaya</span></div>
                    </div>

                </div>

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
    const deliveryCostInfo = document.getElementById('deliveryCostInfo');
    const notesTextarea  = document.getElementById('checkout_notes');
    const charCounter    = document.getElementById('char_counter');

    const formatRupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

    const areaSelect = document.getElementById('shipping_area');

    const updateTotal = function () {
        const selectedDelivery = document.querySelector('input[name="delivery_method"]:checked');
        const subtotal = parseInt(subtotalValue.value) || 0;
        
        // 1. Dapatkan biaya ojek berdasarkan kelurahan terpilih (untuk tampilan statis di dalam kartu Ojek)
        let ojekCost = 0;
        if (areaSelect.value !== "") {
            ojekCost = parseInt(areaSelect.options[areaSelect.selectedIndex].dataset.cost) || 0;
            deliveryCostInfo.textContent = formatRupiah(ojekCost);
        } else {
            deliveryCostInfo.textContent = "Pilih kelurahan";
        }

        // 2. Tentukan ongkos kirim aktual yang digunakan untuk total belanja
        let actualShippingCost = 0;
        if (selectedDelivery.value === "ojek_toko") {
            if (areaSelect.value !== "") {
                actualShippingCost = ojekCost;
                shippingText.textContent = formatRupiah(actualShippingCost);
            } else {
                shippingText.textContent = "Pilih Kelurahan";
                grandTotalText.textContent = formatRupiah(subtotal);
                
                // Set visual card active states
                deliveryCards.forEach(card => card.classList.remove("active"));
                selectedDelivery.closest(".delivery-option-card").classList.add("active");
                return;
            }
        } else {
            // Ambil di toko (gratis)
            actualShippingCost = 0;
            shippingText.textContent = "Gratis";
        }

        grandTotalText.textContent = formatRupiah(subtotal + actualShippingCost);

        // Update active class kartu pengiriman
        deliveryCards.forEach(card => card.classList.remove("active"));
        selectedDelivery.closest(".delivery-option-card").classList.add("active");
    };

    // Listeners for shipping area and delivery methods
    deliveryInputs.forEach(i => i.addEventListener('change', updateTotal));
    areaSelect.addEventListener('change', updateTotal);

    // Active visual states for payment cards
    const paymentQrisLabel = document.getElementById('payment_qris_label');
    const paymentCodLabel = document.getElementById('payment_cod_label');
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const isQris = this.value === 'qris';
            paymentQrisLabel.classList.toggle('active', isQris);
            paymentCodLabel.classList.toggle('active', !isQris);
        });
    });

    // Character counter logic for notes textarea
    if (notesTextarea && charCounter) {
        const updateCharCount = function () {
            charCounter.textContent = notesTextarea.value.length + " / 200";
        };
        notesTextarea.addEventListener('input', updateCharCount);
        updateCharCount();
    }

    // Disable tombol submit agar tidak double-submit
    document.querySelector('.checkout-modern-form').addEventListener('submit', function () {
        checkoutBtn.disabled    = true;
        checkoutBtn.innerHTML = '⏳ Memproses...';
    });

    updateTotal();
});
</script>
@endsection