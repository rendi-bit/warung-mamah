@extends('layouts.store')

@section('content')
    <section class="page-shell">
        <div class="container">
            <h1 class="page-title">Dashboard Admin</h1>
            <p class="page-lead">Control center untuk operasional produk, kategori, dan pemesanan.</p>

            <div class="kpi-grid">
                <div class="kpi-card">
                    <span class="kpi-label">Total Pesanan</span>
                    <div class="kpi-value">{{ $totalOrders }}</div>
                    <p class="kpi-help">Jumlah order tercatat pada sistem.</p>
                </div>
                <div class="kpi-card">
                    <span class="kpi-label">Total Produk</span>
                    <div class="kpi-value">{{ $totalProducts }}</div>
                    <p class="kpi-help">Produk aktif dalam katalog.</p>
                </div>
                <div class="kpi-card">
                    <span class="kpi-label">Total Kategori</span>
                    <div class="kpi-value">{{ $totalCategories }}</div>
                    <p class="kpi-help">Kategori dan struktur segmentasi.</p>
                </div>
            </div>

            <div class="card-dashboard">
                <h3 style="margin-bottom: 10px;">Tren Revenue 6 Bulan Terakhir</h3>
                <p class="text-muted" style="margin-bottom: 14px;">Data dihitung langsung dari transaksi `grand_total` setiap bulan.</p>
                <div class="chart-shell">
                    <canvas
                        id="adminRevenueChart"
                        data-labels='@json($monthlyRevenueLabels)'
                        data-values='@json($monthlyRevenueData)'
                    ></canvas>
                </div>
            </div>

            <div class="row-dashboard">
                <div class="card-dashboard">
                    <h3>Kelola Kategori</h3>
                    <p class="text-muted">Tambah, edit, dan rapikan struktur kategori produk.</p>
                    <a href="{{ route('admin.categories.index') }}" class="btn-warung">Masuk</a>
                </div>

                <div class="card-dashboard">
                    <h3>Kelola Produk</h3>
                    <p class="text-muted">Atur katalog, harga, stok, dan deskripsi produk.</p>
                    <a href="{{ route('admin.products.index') }}" class="btn-warung">Masuk</a>
                </div>

                <div class="card-dashboard">
                    <h3>Kelola Pesanan</h3>
                    <p class="text-muted">Pantau order pelanggan dan update status pesanan.</p>
                    <a href="{{ route('admin.orders.index') }}" class="btn-warung">Masuk</a>
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
                        label: 'Revenue (Rp)',
                        data: chartValues,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22, 163, 74, 0.16)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6
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