<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CustomerDataTable $dataTable)
    {
        return $dataTable->render('customers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->data['action'] = route('customers.store');

        return view('customers.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        Customer::create($data);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer has been created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $this->data['customer_data'] = $customer;
        $this->data['action'] = route('customers.update', $customer->uuid);

        return view('customers.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $customer->update($data);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer has been deleted successfully!');
    }
}
