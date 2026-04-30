@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="section-header" style="text-align:left; margin-bottom: 28px;">
            <h2>Keranjang</h2>
            <p>Review item belanja sebelum lanjut ke proses checkout.</p>
        </div>

        @if($items->count())
            <div class="cart-table-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @php
                                $price = $item->variant ? $item->variant->price : $item->product->price;
                                $subtotal = $price * $item->quantity;
                            @endphp

                            <tr>
                                <td>
                                    <div class="cart-product-info">
                                        <strong>{{ $item->product->name }}</strong>

                                        @if($item->variant)
                                            <div class="cart-variant-badge">
                                                Varian: {{ $item->variant->variant_name }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    Rp {{ number_format($price, 0, ',', '.') }}
                                </td>

                                <td>
                                    <form action="{{ route('cart.update', $item->id) }}" method="POST" class="cart-qty-form">
                                        @csrf
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1">
                                        <button type="submit" class="btn btn-light btn-sm">Update</button>
                                    </form>
                                </td>

                                <td>
                                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                                </td>

                                <td>
                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @php
                $grandTotal = 0;
                foreach($items as $item) {
                    $price = $item->variant ? $item->variant->price : $item->product->price;
                    $grandTotal += $price * $item->quantity;
                }
            @endphp

            <div class="cart-summary-box">
                <h3>Total Belanja</h3>
                <div class="cart-summary-total">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </div>

                <div class="cart-summary-actions">
                    <a href="{{ route('products.index') }}" class="btn btn-light">Lanjut Belanja</a>
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary">Lanjut Checkout</a>
                </div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">🛒</div>
                <h3>Keranjang masih kosong</h3>
                <p>Yuk pilih produk favoritmu terlebih dahulu.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Belanja Sekarang</a>
            </div>
        @endif
    </div>
</section>
@endsection