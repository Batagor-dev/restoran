<?php

namespace App\Http\Controllers;

use App\DataTables\ProductDataTable;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;

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

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        // Handle image upload jika ada
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dibuat!');
    }

    public function edit(Product $product)
    {
        $this->data['product'] = $product;
        $this->data['categories'] = ProductCategory::where('is_active', true)->get();
        $this->data['action'] = route('products.update', $product->uuid);

        return view('products.form', $this->data);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        if ($request->hasFile('image')) {
            // Hapus image lama jika ada
            if ($product->image && file_exists(storage_path('app/public/' . $product->image))) {
                unlink(storage_path('app/public/' . $product->image));
            }
            $data['image'] = $request->file('image')->storage('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diupdate!');
    }

    public function destroy(Product $product)
    {
        // Hapus image jika ada
        if ($product->image && file_exists(storage_path('app/public/' . $product->image))) {
            unlink(storage_path('app/public/' . $product->image));
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dihapus!');
    }
}