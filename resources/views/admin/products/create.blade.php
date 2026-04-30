@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="settings-card">
            <div class="settings-card-header">
                <h3>Tambah Produk</h3>
                <p>Tambahkan produk baru beserta varian ukuran / berat.</p>
            </div>

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="form-warung">
                @csrf

                <label>Nama Produk</label>
                <input type="text" name="name" required>

                <label>Harga Utama</label>
                <input type="number" name="price" required>

                <label>Stok Utama</label>
                <input type="number" name="stock_quantity" required>

                <label>Kategori</label>
                <select name="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                    @endforeach
                </select>

                <label>Deskripsi</label>
                <textarea name="description" rows="4"></textarea>

                <label>Gambar</label>
                <input type="file" name="image">

                <div class="admin-variant-box">
                    <h3>Varian Produk</h3>
                    <p class="text-muted">Tambahkan ukuran / berat seperti 1/4 kg, 1/2 kg, 1 kg.</p>

                    <div id="variant-wrapper">
                        <div class="variant-row">
                            <input type="text" name="variants[0][variant_name]" placeholder="Contoh: 1/4 kg">
                            <input type="number" name="variants[0][price]" placeholder="Harga">
                            <input type="number" name="variants[0][stock]" placeholder="Stok">
                        </div>
                    </div>

                    <button type="button" id="add-variant-btn" class="btn btn-light" style="margin-top: 14px;">
                        + Tambah Varian
                    </button>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn-warung">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let variantIndex = 1;
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