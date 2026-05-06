@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">

        <div class="admin-page-header">
            <div class="admin-page-title">
                <h1>Kelola Produk</h1>
                <p>Atur produk, stok, harga, gambar, dan varian penjualan toko.</p>
            </div>

            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                + Tambah Produk
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($products->count())
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Varian</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="admin-product-mini">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                        @else
                                            <div class="admin-product-placeholder">📦</div>
                                        @endif

                                        <div>
                                            <strong>{{ $product->name }}</strong>
                                            <span>{{ \Illuminate\Support\Str::limit($product->description, 55) }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $product->category->category_name ?? '-' }}</td>

                                <td>
                                    <strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong>
                                </td>

                                <td>
                                    <span class="admin-badge brown">
                                        {{ $product->stock_label }}
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-badge blue">
                                        {{ $product->variants->count() }} varian
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-badge green">
                                        {{ ucfirst($product->status ?? 'active') }}
                                    </span>
                                </td>

                                <td>
                                    <div class="admin-row-actions">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-light btn-sm">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin hapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            @if($product->variants->count())
                                <tr>
                                    <td colspan="7">
                                        <div class="variant-detail-wrap">
                                            @foreach($product->variants as $variant)
                                                <div class="variant-detail-card">
                                                    <strong>{{ $variant->variant_name }}</strong>
                                                    <span>Rp {{ number_format($variant->price, 0, ',', '.') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="admin-empty">
                <div class="admin-empty-icon">📦</div>
                <h3>Belum ada produk</h3>
                <p>Tambahkan produk pertama untuk mulai mengelola toko.</p>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Tambah Produk</a>
            </div>
        @endif

    </div>
</section>
@endsection