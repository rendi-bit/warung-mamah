@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="settings-card">
            <div class="settings-card-header">
                <h3>Edit Produk</h3>
                <p>Update data produk dan variannya.</p>
            </div>

            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="settings-form">
                @csrf
                @method('PUT')

                <div class="profile-form-grid">
                    <div class="settings-input-group">
                        <label>Nama Produk</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
                    </div>

                    <div class="settings-input-group">
                        <label>Kategori</label>
                        <select name="category_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="settings-input-group">
                        <label>Harga Utama</label>
                        <input type="number" name="price" value="{{ old('price', $product->price) }}" required>
                    </div>

                    <div class="settings-input-group">
                        <label>Stok Utama</label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                    </div>

                    <div class="settings-input-group full-width">
                        <label>Deskripsi</label>
                        <textarea name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="settings-input-group full-width">
                        <label>Gambar Produk</label>
                        <input type="file" name="image">
                    </div>
                </div>

                <div class="admin-variant-box">
                    <h3>Varian Produk</h3>
                    <p class="text-muted">Edit ukuran / berat produk di bawah ini.</p>

                    <div id="variant-wrapper">
                        @forelse($product->variants as $index => $variant)
                            <div class="variant-row">
                                <input type="text" name="variants[{{ $index }}][variant_name]" value="{{ $variant->variant_name }}" placeholder="Contoh: 1/4 kg">
                                <input type="number" name="variants[{{ $index }}][price]" value="{{ $variant->price }}" placeholder="Harga">
                                <input type="number" name="variants[{{ $index }}][stock]" value="{{ $variant->stock }}" placeholder="Stok">
                            </div>
                        @empty
                            <div class="variant-row">
                                <input type="text" name="variants[0][variant_name]" placeholder="Contoh: 1/4 kg">
                                <input type="number" name="variants[0][price]" placeholder="Harga">
                                <input type="number" name="variants[0][stock]" placeholder="Stok">
                            </div>
                        @endforelse
                    </div>

                    <button type="button" id="add-variant-btn" class="btn btn-light" style="margin-top:14px;">
                        + Tambah Varian
                    </button>
                </div>

                <div class="settings-form-actions">
                    <button type="submit" class="btn btn-primary settings-save-btn">Update Produk</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let variantIndex = {{ $product->variants->count() }};
    const wrapper = document.getElementById('variant-wrapper');
    const addBtn = document.getElementById('add-variant-btn');

    addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.classList.add('variant-row');

        row.innerHTML = `
            <input type="text" name="variants[${variantIndex}][variant_name]" placeholder="Contoh: 1/2 kg">
            <input type="number" name="variants[${variantIndex}][price]" placeholder="Harga">
            <input type="number" name="variants[${variantIndex}][stock]" placeholder="Stok">
        `;

        wrapper.appendChild(row);
        variantIndex++;
    });
});
</script>
@endsection