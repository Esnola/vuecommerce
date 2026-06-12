<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
       $products = Product::with('categories')
         ->with('creator')
         ->with('updater')
         ->with('deleter')
       ->paginate(20);
       return view('products.index', ['products' => $products,
         'title' => __('Product List')]);
    }

    public function create()
    {
       return view('products.create', ['title' => __('Create Product')]);
    }
  
  
  public function store(StoreProductRequest $request)
  {
    $product = Product::create($request->validated());
    
    return view('products.show', [
      'product' => $product,
      'title' => __('Product Detail')
    ])->with('success', 'New Product created successfully.');
  }

    public function show(Product $product)
    {
      
      return view('products.show', [
        'product' => $product,
        'title' => __('Product Detail')
      ]);
    }

    public function edit(Product $product)
    {
       return view('products.create', [
         'product' => $product,
         'title' => 'Edit Product'
       ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
      $product->update($request->validated());
      return redirect()->route('products.show', $product)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
      $product->delete();
      return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
