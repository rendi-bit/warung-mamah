<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ─────────────────────────────────────────
        // STAT CARDS — cache 5 menit
        // Data ini tidak perlu real-time per detik
        // ─────────────────────────────────────────
        $stats = Cache::remember('admin_dashboard_stats', 300, function () {

            $totalOrders     = Order::count();
            $totalProducts   = Product::count();
            $totalCategories = Category::count();

            // ✅ FIX #2: Pakai kolom 'role' langsung, tidak pakai whereHas relasi
            // Sesuaikan 'role' dan nilai 'user' dengan kolom di tabel users kamu
            $totalCustomers = User::where('role_id', 'user')->count();

            $todayRevenue = Order::where('payment_status', 'paid')
                ->whereDate('created_at', today())
                ->sum('grand_total');

            $monthlyRevenue = Order::where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('grand_total');

            $newOrdersCount = Order::where('order_status', 'pending')->count();

            return compact(
                'totalOrders',
                'totalProducts',
                'totalCategories',
                'totalCustomers',
                'todayRevenue',
                'monthlyRevenue',
                'newOrdersCount'
            );
        });

        // ─────────────────────────────────────────
        // CHART REVENUE 6 BULAN
        // ✅ FIX #1: 1 query groupBy, bukan 6 query dalam loop
        // ─────────────────────────────────────────
        $revenueRows = Cache::remember('admin_dashboard_revenue_chart', 300, function () {
            return Order::where('payment_status', 'paid')
                ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(grand_total) as total')
                )
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(fn($r) => $r->year . '-' . $r->month);
        });

        $monthlyRevenueLabels = [];
        $monthlyRevenueData   = [];

        for ($i = 5; $i >= 0; $i--) {
            $month  = now()->subMonths($i);
            $key    = $month->year . '-' . $month->month;

            $monthlyRevenueLabels[] = $month->translatedFormat('M Y');
            $monthlyRevenueData[]   = (int) ($revenueRows[$key]->total ?? 0);
        }

        // ─────────────────────────────────────────
        // DATA TABEL — cache 2 menit
        // ─────────────────────────────────────────
        $lowStockProducts = Cache::remember('admin_dashboard_lowstock', 120, function () {
            return Product::with('category')
                ->where('stock_quantity', '<=', 5)
                ->latest()
                ->take(6)
                ->get();
        });

        $bestSellingProducts = Cache::remember('admin_dashboard_bestselling', 300, function () {
            return Product::query()
                ->select(
                    'products.id',
                    'products.name',
                    'products.image',
                    DB::raw('SUM(order_items.quantity) as total_sold')
                )
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.payment_status', 'paid')
                ->groupBy('products.id', 'products.name', 'products.image')
                ->orderByDesc('total_sold')
                ->take(5)
                ->get();
        });

        // Order & customer terbaru — tidak di-cache agar selalu fresh
        $latestOrders = Order::with('user')
            ->latest()
            ->take(6)
            ->get();

        // ✅ FIX #3: Filter hanya role 'user', admin tidak masuk daftar
        $latestCustomers = User::where('role_id', 'user')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', array_merge($stats, compact(
            'lowStockProducts',
            'latestOrders',
            'latestCustomers',
            'bestSellingProducts',
            'monthlyRevenueLabels',
            'monthlyRevenueData'
        )));
    }
}