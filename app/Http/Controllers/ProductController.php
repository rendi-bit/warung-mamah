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

        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::all();

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
}   