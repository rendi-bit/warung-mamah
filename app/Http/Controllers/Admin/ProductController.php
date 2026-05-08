<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants'])->latest()->get();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock_mode' => 'required|in:satuan,dus',
            'stock_unit' => 'required|string|max:20',
            'stock_quantity' => 'required_if:stock_mode,satuan|nullable|integer|min:0',
            'unit_per_box' => 'required_if:stock_mode,dus|nullable|integer|min:1',
            'box_stock' => 'required_if:stock_mode,dus|nullable|integer|min:0',
            'restock_estimation' => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',

            'variants.*.variant_name' => 'nullable|string|max:255',
            'variants.*.price' => 'nullable|numeric',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $stockQuantity = 0;

        if ($request->stock_mode === 'dus') {
            $stockQuantity = (int) $request->unit_per_box * (int) $request->box_stock;
        } else {
            $stockQuantity = (int) $request->stock_quantity;
        }

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $stockQuantity,
            'stock_unit' => $request->stock_unit,
            'stock_mode' => $request->stock_mode,
            'unit_per_box' => $request->stock_mode === 'dus' ? $request->unit_per_box : null,
            'box_stock' => $request->stock_mode === 'dus' ? $request->box_stock : null,
            'restock_estimation' => $request->restock_estimation,
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
            'image' => $imagePath,
            'status' => 'active',
        ]);

        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                if (!empty($variant['variant_name']) && !empty($variant['price'])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'variant_name' => $variant['variant_name'],
                        'price' => $variant['price'],
                        'stock' => 0,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $product->load('variants');
        $categories = Category::all();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock_mode' => 'required|in:satuan,dus',
            'stock_unit' => 'required|string|max:20',
            'stock_quantity' => 'required_if:stock_mode,satuan|nullable|integer|min:0',
            'unit_per_box' => 'required_if:stock_mode,dus|nullable|integer|min:1',
            'box_stock' => 'required_if:stock_mode,dus|nullable|integer|min:0',
            'restock_estimation' => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',

            'variants.*.variant_name' => 'nullable|string|max:255',
            'variants.*.price' => 'nullable|numeric',
        ]);

        $imagePath = $product->image;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $stockQuantity = 0;

        if ($request->stock_mode === 'dus') {
            $stockQuantity = (int) $request->unit_per_box * (int) $request->box_stock;
        } else {
            $stockQuantity = (int) $request->stock_quantity;
        }

        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . $product->id,
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $stockQuantity,
            'stock_unit' => $request->stock_unit,
            'stock_mode' => $request->stock_mode,
            'unit_per_box' => $request->stock_mode === 'dus' ? $request->unit_per_box : null,
            'box_stock' => $request->stock_mode === 'dus' ? $request->box_stock : null,
            'restock_estimation' => $request->restock_estimation,
            'category_id' => $request->category_id,
            'image' => $imagePath,
        ]);

        $product->variants()->delete();

        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                if (!empty($variant['variant_name']) && !empty($variant['price'])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'variant_name' => $variant['variant_name'],
                        'price' => $variant['price'],
                        'stock' => 0,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}