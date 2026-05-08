@extends('layouts.store')

@section('content')
<section class="page-shell admin-dashboard-page">
    <div class="container">
        <div class="admin-dashboard-hero">
            <div>
                <span class="admin-hero-badge">Admin Control Center</span>
                <h1>Dashboard Admin</h1>
                <p>
                    Pantau penjualan, pesanan, stok produk, pelanggan terbaru, dan aktivitas toko dalam satu halaman.
                </p>
            </div>

            <div class="admin-hero-actions">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    + Tambah Produk
                </a>

                <a href="{{ route('admin.orders.index') }}" class="btn btn-light">
                    Lihat Pesanan
                </a>
            </div>
        </div>

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

        <div class="admin-dashboard-grid">
            <div class="admin-chart-card">
                <div class="admin-card-head">
                    <div>
                        <h3>Tren Penjualan 6 Bulan Terakhir</h3>
                        <p>Data dihitung dari transaksi yang sudah berstatus paid.</p>
                    </div>
                </div>

                <div class="chart-shell premium-chart-shell">
                    <canvas
                        id="adminRevenueChart"
                        data-labels='@json($monthlyRevenueLabels)'
                        data-values='@json($monthlyRevenueData)'
                    ></canvas>
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
                    </a>

                    <a href="{{ route('admin.products.index') }}">
                        <i class="fas fa-box-open"></i>
                        <span>Kelola Produk</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}">
                        <i class="fas fa-layer-group"></i>
                        <span>Kelola Kategori</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}">
                        <i class="fas fa-receipt"></i>
                        <span>Kelola Pesanan</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="admin-dashboard-grid two-column">
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
                        <div class="admin-mini-row">
                            <div>
                                <strong>{{ $order->order_code }}</strong>
                                <span>{{ $order->user->name ?? 'User' }} • {{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>

                            <div class="admin-mini-right">
                                <b>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</b>

                                <small class="admin-status-pill {{ $order->payment_method === 'qris' ? 'blue' : 'orange' }}">
                                    {{ $order->payment_method === 'qris' ? 'QRIS' : 'COD' }}
                                </small>

                                @if($order->payment_method === 'cod')
                                    <small class="admin-status-pill orange">
                                        Bayar di Tempat
                                    </small>
                                @else
                                    <small class="admin-status-pill {{ $order->payment_status === 'paid' ? 'green' : 'yellow' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty-mini">
                            Belum ada pesanan terbaru.
                        </div>
                    @endforelse
                </div>
            </div>

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
                            Stok produk masih aman.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="admin-dashboard-grid two-column">
            <div class="admin-list-card">
                <div class="admin-card-head">
                    <div>
                        <h3>Produk Terlaris</h3>
                        <p>Produk dengan jumlah penjualan terbanyak.</p>
                    </div>
                </div>

                <div class="admin-mini-list">
                    @forelse($bestSellingProducts as $product)
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
                                <span>Terjual {{ $product->total_sold }} item</span>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty-mini">
                            Belum ada produk terlaris.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="admin-list-card">
                <div class="admin-card-head">
                    <div>
                        <h3>Pelanggan Terbaru</h3>
                        <p>User terbaru yang terdaftar di toko.</p>
                    </div>
                </div>

                <div class="admin-mini-list">
                    @forelse($latestCustomers as $customer)
                        <div class="admin-mini-row">
                            <div>
                                <strong>{{ $customer->name }}</strong>
                                <span>{{ $customer->email }}</span>
                            </div>

                            <small>
                                {{ $customer->created_at->format('d M Y') }}
                            </small>
                        </div>
                    @empty
                        <div class="admin-empty-mini">
                            Belum ada pelanggan terbaru.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('adminRevenueChart');

    if (!ctx || typeof Chart === 'undefined') return;

    const chartLabels = JSON.parse(ctx.dataset.labels || '[]');
    const chartValues = JSON.parse(ctx.dataset.values || '[]');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Revenue',
                data: chartValues,
                borderColor: '#8B4513',
                backgroundColor: 'rgba(139, 69, 19, 0.14)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    ticks: {
                        callback: function (value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endsection