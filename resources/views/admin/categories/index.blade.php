@extends('layouts.store')

@section('content')
<section class="section">
    <div class="container">

        <div class="admin-page-header">
            <div class="admin-page-title">
                <h1>Kelola Kategori</h1>
                <p>Atur kategori produk agar katalog toko lebih rapi dan mudah dicari pelanggan.</p>
            </div>

            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                + Tambah Kategori
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($categories->count())
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Slug</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>
                                    <div class="admin-product-mini">
                                        <div class="admin-product-placeholder">🏷️</div>

                                        <div>
                                            <strong>{{ $category->category_name }}</strong>
                                            <span>Kategori produk TOKO TIKA</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="admin-badge brown">
                                        {{ $category->slug ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    {{ $category->created_at ? $category->created_at->format('d M Y') : '-' }}
                                </td>

                                <td>
                                    <div class="admin-row-actions">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-light btn-sm">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="admin-empty">
                <div class="admin-empty-icon">🏷️</div>
                <h3>Belum ada kategori</h3>
                <p>Buat kategori pertama agar produk lebih mudah dikelompokkan.</p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Tambah Kategori</a>
            </div>
        @endif

    </div>
</section>
@endsection