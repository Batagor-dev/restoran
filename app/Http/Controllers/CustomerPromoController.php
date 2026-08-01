<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerPromoDataTable;
use App\Http\Requests\StoreCustomerPromoRequest;
use App\Http\Requests\UpdateCustomerPromoRequest;
use App\Models\CustomerPromo;

class CustomerPromoController extends Controller
{
    public function index(CustomerPromoDataTable $dataTable)
    {
        return $dataTable->render('customer-promos.index');
    }

    public function create()
    {
        $this->data['action'] = route('customer-promos.store');

        return view('customer-promos.form', $this->data);
    }

    public function store(StoreCustomerPromoRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        CustomerPromo::create($data);

        return redirect()
            ->route('customer-promos.index')
            ->with('success', 'Customer promo has been created successfully!');
    }

    public function edit(CustomerPromo $customerPromo)
    {
        $this->data['customerPromo'] = $customerPromo;
        $this->data['action'] = route('customer-promos.update', $customerPromo->uuid);

        return view('customer-promos.form', $this->data);
    }

    public function update(UpdateCustomerPromoRequest $request, CustomerPromo $customerPromo)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $customerPromo->update($data);

        return redirect()
            ->route('customer-promos.index')
            ->with('success', 'Customer promo has been updated successfully!');
    }

    public function destroy(CustomerPromo $customerPromo)
    {
        $customerPromo->delete();

        return redirect()
            ->route('customer-promos.index')
            ->with('success', 'Customer promo has been deleted successfully!');
    }
}