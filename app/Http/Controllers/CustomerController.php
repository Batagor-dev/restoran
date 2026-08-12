<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(CustomerDataTable $dataTable)
    {
        return $dataTable->render('customers.index');
    }

    public function create()
    {
        $this->data['action'] = route('customers.store');
        return view('customers.form', $this->data);
    }

    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        Customer::create($data);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer has been created successfully!');
    }

    public function edit(Customer $customer)
    {
        $this->data['customer'] = $customer;
        $this->data['action'] = route('customers.update', $customer->uuid);

        return view('customers.form', $this->data);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $customer->update($data);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer has been updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer has been deleted successfully!');
    }
}