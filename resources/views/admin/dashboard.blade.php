@extends('layouts.store')

@section('content')
<section class="page-shell admin-dashboard-page">
    <div class="container">

        {{-- HEADER --}}
        <div class="admin-dashboard-hero">
            <div>
                <span class="admin-hero-badge">Admin Control Center</span>
                <h1>Dashboard Admin</h1>
                <p>Pantau penjualan, pesanan, stok produk, dan aktivitas toko dalam satu halaman.</p>
            </div>
            <div class="admin-hero-actions">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Tambah Produk</a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-light">Lihat Pesanan</a>
            </div>
        </div>

        {{-- 4 KPI CARDS --}}
        <div class="admin-kpi-premium-grid">
            <div class="admin-kpi-premium-card">
                <div class="admin-kpi-icon brown">
                    <i class="fas fa-bag-shopping"></i>
                </div>
                <span>Total Pesanan</span>
                <strong>{{ $totalOrders }}</strong>
                <p>Jumlah order tercatat pada sistem.</p>
            </div>

            <div class="admin-kpi-premium-card">
                <div class="admin-kpi-icon green">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <span>Pemasukan Hari Ini</span>
                <strong>Rp {{ number_format($todayRevenue ?? 0, 0, ',', '.') }}</strong>
                <p>Total pemasukan dari order yang sudah dibayar hari ini.</p>
            </div>

            <div class="admin-kpi-premium-card">
                <div class="admin-kpi-icon blue">
                    <i class="fas fa-calendar-days"></i>
                </div>
                <span>Pemasukan Bulan Ini</span>
                <strong>Rp {{ number_format($monthlyRevenue ?? 0, 0, ',', '.') }}</strong>
                <p>Total pemasukan bulan berjalan.</p>
            </div>

            <div class="admin-kpi-premium-card">
                <div class="admin-kpi-icon orange">
                    <i class="fas fa-bell"></i>
                </div>
                <span>Order Baru</span>
                <strong>{{ $newOrdersCount ?? 0 }}</strong>
                <p>Pesanan pending yang perlu dicek admin.</p>
            </div>
        </div>

        {{-- PESANAN TERBARU + QUICK ACTION --}}
        <div class="admin-dashboard-grid">
            <div class="admin-list-card">
                <div class="admin-card-head">
                    <div>
                        <h3>Pesanan Terbaru</h3>
                        <p>Order terbaru yang masuk ke sistem.</p>
                    </div>
                    <a href="{{ route('admin.orders.index') }}">Lihat semua</a>
                </div>
                <div class="admin-mini-list">
                    @forelse($latestOrders as $order)
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="admin-mini-row admin-mini-row-link">
                            <div class="admin-order-code-badge">{{ $order->order_code }}</div>
                            <div>
                                <strong>{{ $order->user->name ?? 'User' }}</strong>
                                <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="admin-mini-right">
                                <b>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</b>
                                <small class="admin-status-pill {{ $order->payment_method === 'qris' ? 'blue' : 'orange' }}">
                                    {{ strtoupper($order->payment_method) }}
                                </small>
                                <small class="admin-status-pill {{ $order->payment_status === 'paid' ? 'green' : 'yellow' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </small>
                            </div>
                        </a>
                    @empty
                        <div class="admin-empty-mini">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada pesanan terbaru.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="admin-side-card">
                <div class="admin-card-head">
                    <div>
                        <h3>Quick Action</h3>
                        <p>Akses cepat untuk mengelola toko.</p>
                    </div>
                </div>
                <div class="admin-quick-list">
                    <a href="{{ route('admin.products.create') }}">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Produk</span>
                        <i class="fas fa-chevron-right admin-quick-arrow"></i>
                    </a>
                    <a href="{{ route('admin.products.index') }}">
                        <i class="fas fa-box-open"></i>
                        <span>Kelola Produk</span>
                        <i class="fas fa-chevron-right admin-quick-arrow"></i>
                    </a>
                    <a href="{{ route('admin.categories.index') }}">
                        <i class="fas fa-layer-group"></i>
                        <span>Kelola Kategori</span>
                        <i class="fas fa-chevron-right admin-quick-arrow"></i>
                    </a>
                    <a href="{{ route('admin.orders.index') }}">
                        <i class="fas fa-receipt"></i>
                        <span>Kelola Pesanan</span>
                        <i class="fas fa-chevron-right admin-quick-arrow"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- STOK HAMPIR HABIS + PRODUK TERLARIS --}}
        <div class="admin-dashboard-grid two-column">
            <div class="admin-list-card">
                <div class="admin-card-head">
                    <div>
                        <h3>Stok Hampir Habis</h3>
                        <p>Produk dengan stok 5 atau kurang.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}">Kelola</a>
                </div>
                <div class="admin-mini-list">
                    @forelse($lowStockProducts as $product)
                        <div class="admin-product-alert-row">
                            <div class="admin-product-alert-img">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <i class="fas fa-box"></i>
                                @endif
                            </div>
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <span>{{ $product->category->category_name ?? '-' }}</span>
                            </div>
                            <small class="admin-stock-warning">
                                {{ $product->stock_quantity }} {{ $product->stock_unit }}
                            </small>
                        </div>
                    @empty
                        <div class="admin-empty-mini">
                            <i class="fas fa-check-circle" style="color:#10b981;font-size:20px;"></i>
                            <p>Stok produk masih aman.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="admin-list-card">
                <div class="admin-card-head">
                    <div>
                        <h3>Produk Terlaris</h3>
                        <p>Produk dengan jumlah penjualan terbanyak.</p>
                    </div>
                </div>
                <div class="admin-mini-list">
                    @forelse($bestSellingProducts as $index => $product)
                        <div class="admin-product-alert-row">
                            <div class="admin-rank-badge rank-{{ $index + 1 }}">
                                #{{ $index + 1 }}
                            </div>
                            <div class="admin-product-alert-img">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <i class="fas fa-box"></i>
                                @endif
                            </div>
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <span>Terjual <b>{{ $product->total_sold }}</b> item</span>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty-mini">
                            <i class="fas fa-chart-bar" style="color:#d1d5db;font-size:20px;"></i>
                            <p>Belum ada produk terlaris.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- PELANGGAN TERBARU --}}
        <div class="admin-list-card" style="margin-top:24px;">
            <div class="admin-card-head">
                <div>
                    <h3>Pelanggan Terbaru</h3>
                    <p>User terbaru yang terdaftar di toko.</p>
                </div>
            </div>
            <div class="admin-customer-grid">
                @forelse($latestCustomers as $customer)
                    <div class="admin-customer-card">
                        <div class="admin-customer-avatar">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                        <div>
                            <strong>{{ $customer->name }}</strong>
                            <span>{{ $customer->email }}</span>
                            <small>{{ $customer->created_at->format('d M Y') }}</small>
                        </div>
                    </div>
                @empty
                    <div class="admin-empty-mini" style="grid-column:1/-1;">
                        <i class="fas fa-users" style="color:#d1d5db;font-size:20px;"></i>
                        <p>Belum ada pelanggan terbaru.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</section>
@endsection