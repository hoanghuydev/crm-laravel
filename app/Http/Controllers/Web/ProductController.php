<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Models\Product;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'stock_status' => $request->get('stock_status'),
        ];

        $products = $this->productService->getAllProducts($filters, 15);

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user can create products
        if (Gate::denies('create-product')) {
            abort(403, 'Unauthorized. Only admin can create products.');
        }

        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request)
    {
        // Check if user can create products
        if (Gate::denies('create-product')) {
            abort(403, 'Unauthorized. Only admin can create products.');
        }

        try {
            $product = $this->productService->createProduct($request->validated());
            return redirect()->route('products.show', $product)
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product = $this->productService->getProductById($product->id);
        
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        // Check if user can update products
        if (Gate::denies('update-product')) {
            abort(403, 'Unauthorized. Only admin can edit products.');
        }

        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        // Check if user can update products
        if (Gate::denies('update-product')) {
            abort(403, 'Unauthorized. Only admin can update products.');
        }

        try {
            $updatedProduct = $this->productService->updateProduct($product->id, $request->validated());
            return redirect()->route('products.show', $updatedProduct)
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Check if user can delete products
        if (Gate::denies('delete-product')) {
            abort(403, 'Unauthorized. Only admin can delete products.');
        }

        try {
            // Set status to inactive instead of deleting
            $this->productService->updateProduct($product->id, ['status' => 'inactive']);
            return redirect()->route('products.index')
                ->with('success', 'Product deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get low stock products
     */
    public function lowStock()
    {
        $products = $this->productService->getLowStockProducts(10);
        return view('products.low-stock', compact('products'));
    }
}
