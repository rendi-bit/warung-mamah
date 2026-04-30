<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MidtransCallbackController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\ChatbotController;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Carbon;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Tambahkan route untuk /settings
Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
Route::put('/settings/profile', [\App\Http\Controllers\SettingController::class, 'updateProfile'])->name('settings.profile.update');
Route::put('/settings/password', [\App\Http\Controllers\SettingController::class, 'updatePassword'])->name('settings.password.update');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');

    Route::get('/my-orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{id}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');

    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/products/{product}/reviews', [\App\Http\Controllers\ProductReviewController::class, 'store'])
    ->name('products.reviews.store')
    ->middleware('auth');

    
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        $months = collect(range(5, 0))->map(function ($offset) {
            return Carbon::now()->subMonths($offset);
        })->push(Carbon::now());

        $monthlyRevenue = $months->map(function ($month) {
            return (float) Order::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('grand_total');
        });

        $chartLabels = $months->map(fn ($month) => $month->translatedFormat('M Y'));

        return view('admin.dashboard', [
            'totalOrders' => Order::count(),
            'totalProducts' => Product::count(),
            'totalCategories' => Category::count(),
            'monthlyRevenueLabels' => $chartLabels,
            'monthlyRevenueData' => $monthlyRevenue,
        ]);
    })->name('dashboard');

    Route::resource('/categories', AdminCategoryController::class);
    Route::resource('/products', AdminProductController::class);
    Route::resource('/orders', AdminOrderController::class)->only(['index', 'show', 'update']);
});

Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/payment/{order}', [CheckoutController::class, 'payment'])->name('checkout.payment');
});


Route::middleware('auth')->group(function () {
    Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/add/{product}', [\App\Http\Controllers\WishlistController::class, 'add'])->name('wishlist.add');
    Route::delete('/wishlist/remove/{id}', [\App\Http\Controllers\WishlistController::class, 'remove'])->name('wishlist.remove');
    
                                                                                                                              
});

Route::post('/midtrans/callback', [MidtransCallbackController::class, 'handle'])->name('midtrans.callback');


require __DIR__.'/auth.php';