@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">
        <div class="admin-page-header">
            <div class="admin-page-title">
                <h1>Edit Kategori</h1>
                <p>Perbarui nama kategori produk toko.</p>
            </div>

            <a href="{{ route('admin.categories.index') }}" class="btn btn-light">
                Kembali
            </a>
        </div>

        <div class="admin-action-card">
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" class="form-warung">
                @csrf
                @method('PUT')

                <label>Nama Kategori</label>
                <input type="text" name="category_name" value="{{ old('category_name', $category->category_name) }}" required>

                <button type="submit" class="btn-warung">
                    Update Kategori
                </button>
            </form>
        </div>
    </div>
</section>
@endsection