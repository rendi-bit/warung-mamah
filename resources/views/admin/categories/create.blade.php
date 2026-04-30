@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="admin-page-header">
            <div class="admin-page-title">
                <h1>Tambah Kategori</h1>
                <p>Buat kategori baru untuk mengelompokkan produk toko.</p>
            </div>

            <a href="{{ route('admin.categories.index') }}" class="btn btn-light">
                Kembali
            </a>
        </div>

        <div class="admin-action-card">
            <form action="{{ route('admin.categories.store') }}" method="POST" class="form-warung">
                @csrf

                <label>Nama Kategori</label>
                <input type="text" name="category_name" placeholder="Contoh: Bumbu Dapur" required>

                <button type="submit" class="btn-warung">
                    Simpan Kategori
                </button>
            </form>
        </div>
    </div>
</section>
@endsection