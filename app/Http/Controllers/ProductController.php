<?php

namespace App\Http\Controllers;

use App\DataTables\ProductDataTable;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\ImageService;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(ProductDataTable $dataTable)
    {
        return $dataTable->render('products.index');
    }

    public function create()
    {
        $this->data['categories'] = ProductCategory::where('is_active', true)->get();
        $this->data['action'] = route('products.store');

        return view('products.form', $this->data);
    }

    public function store(StoreProductRequest $request, ImageService $imageService)
    {
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $compressed = $imageService->compress($file);
            $filename = 'product-images/'.uniqid().'.jpg';
            Storage::disk('public')->put($filename, $compressed);
            $data['image'] = $filename;
        }

        Product::create($data);

        return redirect('products')->with('success', 'New product has been created!');
    }

    public function edit(Product $product)
    {
        $this->data['product'] = $product;
        $this->data['product_data'] = $product;
        $this->data['categories'] = ProductCategory::where('is_active', true)->get();
        $this->data['action'] = route('products.update', $product->uuid);

        return view('products.form', $this->data);
    }

    public function update(UpdateProductRequest $request, Product $product, ImageService $imageService)
    {
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $file = $request->file('image');
            $compressed = $imageService->compress($file);
            $filename = 'product-images/'.uniqid().'.jpg';
            Storage::disk('public')->put($filename, $compressed);
            $data['image'] = $filename;
        }

        $product->update($data);

        return redirect('products')->with('success', 'Product has been updated!');
    }

    public function destroy(Product $product)
    {
        // Hapus image jika ada
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dihapus!');
    }
}