<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductController extends Controller
{
   public function index(Request $request)
    {
        $query = Product::with(['category', 'variants', 'reviews'])
            ->where('status', 'active');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $keyword = $request->search;

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhereHas('category', function ($categoryQuery) use ($keyword) {
                        $categoryQuery->where('category_name', 'LIKE', '%' . $keyword . '%');
                    });
            });
        }

        $products = $query->latest()->paginate(12)->withQueryString();

        $categories = Category::latest()->get();

        return view('products.index', compact('products', 'categories'));
    }
    public function show($slug)
    {
        $product = Product::with(['category', 'variants', 'reviews.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::with(['category', 'variants', 'reviews'])
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->latest()
            ->take(4)
            ->get();

        $recent = session()->get('recent_products', []);

        if (!in_array($product->id, $recent)) {
            array_unshift($recent, $product->id);
        }

        $recent = array_slice($recent, 0, 5);
        session()->put('recent_products', $recent);

        $recentProducts = Product::with(['category', 'variants', 'reviews'])
            ->whereIn('id', $recent)
            ->where('id', '!=', $product->id)
            ->get();

        $reviews = ProductReview::with('user')
            ->where('product_id', $product->id)
            ->latest()
            ->get();

        $avgRating = $reviews->avg('rating');

        return view('products.show', compact(
            'product',
            'relatedProducts',
            'recentProducts',
            'reviews',
            'avgRating'
        ));
    }

    public function search(Request $request)
    {
        $keyword = $request->get('q');

        $products = Product::with('category')
            ->where('status', 'active')
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhereHas('category', function ($categoryQuery) use ($keyword) {
                        $categoryQuery->where('category_name', 'LIKE', '%' . $keyword . '%');
                    });
            })
            ->latest()
            ->limit(8)
            ->get();

        return response()->json($products->map(function ($product) {
            return [
                'name' => $product->name,
                'category' => $product->category->category_name ?? 'Tanpa Kategori',
                'price' => 'Rp ' . number_format($product->display_price ?? $product->price, 0, ',', '.'),
                'image' => $product->image ? asset('storage/' . $product->image) : null,
                'url' => route('products.show', $product->slug),
            ];
        }));
    }
}   