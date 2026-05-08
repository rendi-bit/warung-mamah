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

                <div class="stock-system-box">
                    <h3>Pengaturan Stok</h3>
                    <p class="text-muted">Pilih cara input stok agar tidak membingungkan antara stok satuan dan stok dus.</p>

                    <label>Mode Stok</label>
                    <select name="stock_mode" id="stock_mode" required>
                        <option value="satuan">Stok Per Satuan</option>
                        <option value="dus">Stok Per Dus</option>
                    </select>

                    <label>Jenis Satuan</label>
                    <select name="stock_unit" id="stock_unit" required>
                        <option value="pcs">Pcs</option>
                        <option value="botol">Botol</option>
                        <option value="bungkus">Bungkus</option>
                        <option value="pack">Pack</option>
                        <option value="kg">Kg</option>
                        <option value="gram">Gram</option>
                        <option value="liter">Liter</option>
                        <option value="ikat">Ikat</option>
                    </select>

                    <div id="stock_satuan_box">
                        <label>Total Stok Satuan</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" min="0" placeholder="Contoh: 20">

                        <div class="stock-preview-box">
                            Total stok: <strong id="satuan_preview">0 pcs</strong>
                        </div>
                    </div>

                    <div id="stock_dus_box" style="display:none;">
                        <div class="stock-input-grid">
                            <div>
                                <label>Isi Per Dus</label>
                                <input type="number" name="unit_per_box" id="unit_per_box" min="1" placeholder="Contoh: 12">
                            </div>

                            <div>
                                <label>Stok Dus</label>
                                <input type="number" name="box_stock" id="box_stock" min="0" placeholder="Contoh: 5">
                            </div>
                        </div>

                        <div class="stock-preview-box">
                            Total stok otomatis:
                            <strong id="dus_preview">0 pcs</strong>
                        </div>
                    </div>

                    <label>Estimasi Restok</label>
                    <input type="text" name="restock_estimation" placeholder="Contoh: 1 hari">
                </div>

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