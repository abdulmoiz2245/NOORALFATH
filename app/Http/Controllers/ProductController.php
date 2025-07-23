<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $products = Product::latest()->get();

        return Inertia::render('products/Index', [
            'products' => $products
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('products/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('message', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): Response
    {
        return Inertia::render('products/Show', [
            'product' => $product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Response
    {
        return Inertia::render('products/Edit', [
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('message', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('message', 'Product deleted successfully.');
    }

    /**
     * Search products for API
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        $products = Product::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'sku', 'price']);

        return response()->json($products);
    }
}
