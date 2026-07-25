<?php

namespace App\Http\Controllers;

use App\DataTables\ProductCategoryDataTable;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductCategoryDataTable $dataTable)
    {
        return $dataTable->render('product_categories.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->data['action'] = route('product_categories.store');

        return view('product_categories.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        ProductCategory::create($data);

        return redirect()
            ->route('product_categories.index')
            ->with('success', 'Product Category has been created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $productCategory)
    {
        $this->data['product_category_data'] = $productCategory;
        $this->data['action'] = route('product_categories.update', $productCategory->uuid);

        return view('product_categories.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $productCategory->update($data);

        return redirect()
            ->route('product_categories.index')
            ->with('success', 'Product Category has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        $productCategory->delete();

        return redirect()
            ->route('product_categories.index')
            ->with('success', 'Product Category has been deleted successfully!');
    }
}
