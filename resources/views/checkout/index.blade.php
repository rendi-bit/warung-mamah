@extends('layouts.store')

@section('content')
<section class="page-shell">
    <div class="container">
        <h1 class="page-title">Checkout</h1>
        <p class="page-lead">Konfirmasi item belanja dan lengkapi alamat pengiriman sebelum order diproses.</p>

        <div class="checkout-grid">
            <div class="card-dashboard">
                <h3>Ringkasan Belanja</h3>

                @php $total = 0; @endphp

                <div class="checkout-items-list">
                    @foreach($cart->items as $item)
                        @php
                            $price = $item->variant ? $item->variant->price : $item->product->price;
                            $subtotal = $price * $item->quantity;
                            $total += $subtotal;
                        @endphp

                        <div class="checkout-item-row">
                            <div>
                                <strong>{{ $item->product->name }}</strong>

                                @if($item->variant)
                                    <div class="cart-variant-badge" style="margin-top:6px;">
                                        Varian: {{ $item->variant->variant_name }}
                                    </div>
                                @endif

                                <p>{{ $item->quantity }} x Rp {{ number_format($price, 0, ',', '.') }}</p>
                            </div>

                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="checkout-total-row">
                    <h3>Total</h3>
                    <h3>Rp {{ number_format($total, 0, ',', '.') }}</h3>
                </div>
            </div>

            <form action="{{ route('checkout.process') }}" method="POST" class="form-warung">
                @csrf

                <h3>Informasi Pengiriman</h3>
                <p class="text-muted" style="margin-bottom: 12px;">Pastikan alamat detail agar pesanan cepat sampai.</p>

                <label>Alamat Pengiriman</label>
                <textarea name="shipping_address" required rows="5" placeholder="Contoh: Jl. Mawar No. 12, RT 03/RW 01, Bekasi">{{ old('shipping_address') }}</textarea>

                <div class="checkout-actions">
                    <a href="{{ route('cart.index') }}" class="btn btn-light">Kembali ke Keranjang</a>
                    <button type="submit" class="btn-warung">Proses Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection