<?php

namespace App\Http\Controllers;

use App\DataTables\PromoDataTable;
use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\UpdatePromoRequest;
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

        Promo::create($data);

        return redirect()
            ->route('promo.index')
            ->with('success', 'Promo has been created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promo $promo)
    {
        $this->data['promo_data'] = $promo;
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
}
