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

                    <div class="settings-input-group full-width">
                        <div class="stock-system-box">
                            <h3>Pengaturan Stok</h3>
                            <p class="text-muted">Atur stok berdasarkan satuan atau dus.</p>

                            <label>Mode Stok</label>
                            <select name="stock_mode" id="stock_mode" required>
                                <option value="satuan" {{ old('stock_mode', $product->stock_mode ?? 'satuan') === 'satuan' ? 'selected' : '' }}>
                                    Stok Per Satuan
                                </option>
                                <option value="dus" {{ old('stock_mode', $product->stock_mode ?? 'satuan') === 'dus' ? 'selected' : '' }}>
                                    Stok Per Dus
                                </option>
                            </select>

                            <label>Jenis Satuan</label>
                            <select name="stock_unit" id="stock_unit" required>
                                @php
                                    $units = ['pcs', 'botol', 'bungkus', 'pack', 'kg', 'gram', 'liter', 'ikat'];
                                @endphp

                                @foreach($units as $unit)
                                    <option value="{{ $unit }}" {{ old('stock_unit', $product->stock_unit ?? 'pcs') == $unit ? 'selected' : '' }}>
                                        {{ ucfirst($unit) }}
                                    </option>
                                @endforeach
                            </select>

                            <div id="stock_satuan_box">
                                <label>Total Stok Satuan</label>
                                <input
                                    type="number"
                                    name="stock_quantity"
                                    id="stock_quantity"
                                    min="0"
                                    value="{{ old('stock_quantity', $product->stock_quantity) }}"
                                >

                                <div class="stock-preview-box">
                                    Total stok:
                                    <strong id="satuan_preview">0 pcs</strong>
                                </div>
                            </div>

                            <div id="stock_dus_box" style="display:none;">
                                <div class="stock-input-grid">
                                    <div>
                                        <label>Isi Per Dus</label>
                                        <input
                                            type="number"
                                            name="unit_per_box"
                                            id="unit_per_box"
                                            min="1"
                                            value="{{ old('unit_per_box', $product->unit_per_box) }}"
                                        >
                                    </div>

                                    <div>
                                        <label>Stok Dus</label>
                                        <input
                                            type="number"
                                            name="box_stock"
                                            id="box_stock"
                                            min="0"
                                            value="{{ old('box_stock', $product->box_stock) }}"
                                        >
                                    </div>
                                </div>

                                <div class="stock-preview-box">
                                    Total stok otomatis:
                                    <strong id="dus_preview">0 pcs</strong>
                                </div>
                            </div>

                            <label>Estimasi Restok</label>
                            <input
                                type="text"
                                name="restock_estimation"
                                value="{{ old('restock_estimation', $product->restock_estimation) }}"
                                placeholder="Contoh: 1 hari"
                            >
                        </div>
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
                            </div>
                        @empty
                            <div class="variant-row">
                                <input type="text" name="variants[0][variant_name]" placeholder="Contoh: 1/4 kg">
                                <input type="number" name="variants[0][price]" placeholder="Harga">
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

    if (addBtn && wrapper) {
        addBtn.addEventListener('click', function () {
            const row = document.createElement('div');
            row.classList.add('variant-row');

            row.innerHTML = `
                <input type="text" name="variants[${variantIndex}][variant_name]" placeholder="Contoh: 1/2 kg">
                <input type="number" name="variants[${variantIndex}][price]" placeholder="Harga">
            `;

            wrapper.appendChild(row);
            variantIndex++;
        });
    }

    const stockMode = document.getElementById('stock_mode');
    const stockUnit = document.getElementById('stock_unit');
    const satuanBox = document.getElementById('stock_satuan_box');
    const dusBox = document.getElementById('stock_dus_box');

    const stockQuantity = document.getElementById('stock_quantity');
    const unitPerBox = document.getElementById('unit_per_box');
    const boxStock = document.getElementById('box_stock');

    const satuanPreview = document.getElementById('satuan_preview');
    const dusPreview = document.getElementById('dus_preview');

    function updateStockView() {
        const unit = stockUnit.value || 'pcs';

        if (stockMode.value === 'dus') {
            satuanBox.style.display = 'none';
            dusBox.style.display = 'block';

            const isiDus = parseInt(unitPerBox.value) || 0;
            const jumlahDus = parseInt(boxStock.value) || 0;
            const total = isiDus * jumlahDus;

            dusPreview.textContent = total + ' ' + unit;
        } else {
            satuanBox.style.display = 'block';
            dusBox.style.display = 'none';

            const total = parseInt(stockQuantity.value) || 0;
            satuanPreview.textContent = total + ' ' + unit;
        }
    }

    [stockMode, stockUnit, stockQuantity, unitPerBox, boxStock].forEach(function (element) {
        if (element) {
            element.addEventListener('input', updateStockView);
            element.addEventListener('change', updateStockView);
        }
    });

    updateStockView();
});
</script>
@endsection