<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();

        $totalCustomers = User::whereHas('role', function ($query) {
            $query->where('role_name', 'user');
        })->count();

        $todayRevenue = Order::where('payment_status', 'paid')
            ->whereDate('created_at', today())
            ->sum('grand_total');

        $monthlyRevenue = Order::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('grand_total');

        $newOrdersCount = Order::where('order_status', 'pending')->count();

        $lowStockProducts = Product::with('category')
            ->where('stock_quantity', '<=', 5)
            ->latest()
            ->take(6)
            ->get();

        $latestOrders = Order::with('user')
            ->latest()
            ->take(6)
            ->get();

        $latestCustomers = User::latest()
            ->take(6)
            ->get();

        $bestSellingProducts = Product::query()
            ->select('products.id', 'products.name', 'products.image', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->groupBy('products.id', 'products.name', 'products.image')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $monthlyRevenueLabels = [];
        $monthlyRevenueData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $monthlyRevenueLabels[] = $month->translatedFormat('M Y');

            $monthlyRevenueData[] = (int) Order::where('payment_status', 'paid')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('grand_total');
        }

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalProducts',
            'totalCategories',
            'totalCustomers',
            'todayRevenue',
            'monthlyRevenue',
            'newOrdersCount',
            'lowStockProducts',
            'latestOrders',
            'latestCustomers',
            'bestSellingProducts',
            'monthlyRevenueLabels',
            'monthlyRevenueData'
        ));
    }
}