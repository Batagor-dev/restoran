<?php

namespace App\Http\Controllers;

use App\DataTables\PromoDataTable;
use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\UpdatePromoRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promo;

class PromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PromoDataTable $dataTable)
    {
        return $dataTable->render('promo.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->data['products'] = $this->productOptions();
        $this->data['categories'] = $this->categoryOptions();
        $this->data['action'] = route('promo.store');

        return view('promo.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePromoRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $promo = Promo::create($data);
        $this->syncRelations($promo, $request);

        return redirect()
            ->route('promo.index')
            ->with('success', 'Promo has been created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promo $promo)
    {
        $promo->load(['products', 'categories']);

        $this->data['promo_data'] = $promo;
        $this->data['products'] = $this->productOptions();
        $this->data['categories'] = $this->categoryOptions();
        $this->data['selected_products'] = old('products', $promo->products->pluck('id')->all());
        $this->data['selected_categories'] = old('categories', $promo->categories->pluck('id')->all());
        $this->data['action'] = route('promo.update', $promo->uuid);

        return view('promo.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePromoRequest $request, Promo $promo)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $promo->update($data);
        $this->syncRelations($promo, $request);

        return redirect()
            ->route('promo.index')
            ->with('success', 'Promo has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promo $promo)
    {
        $promo->delete();

        return redirect()
            ->route('promo.index')
            ->with('success', 'Promo has been deleted successfully!');
    }

    private function syncRelations(Promo $promo, StorePromoRequest|UpdatePromoRequest $request): void
    {
        if ($promo->scope === 'product') {
            $promo->products()->sync($request->input('products', []));
        } else {
            $promo->products()->sync([]);
        }

        if ($promo->scope === 'category_product') {
            $promo->categories()->sync($request->input('categories', []));
        } else {
            $promo->categories()->sync([]);
        }
    }

    private function productOptions(): array
    {
        return Product::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => ['value' => $p->id, 'label' => $p->name])
            ->all();
    }

    private function categoryOptions(): array
    {
        return ProductCategory::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (ProductCategory $c) => ['value' => $c->id, 'label' => $c->name])
            ->all();
    }
}
