<?php

namespace App\Http\Controllers;

use App\DataTables\DiningTableDataTable;
use App\Http\Requests\StoreDiningTableRequest;
use App\Http\Requests\UpdateDiningTableRequest;
use App\Models\DiningTable;
use App\Models\Outlet;

class DiningTableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DiningTableDataTable $dataTable)
    {
        return $dataTable->render('tables.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->data['outlets'] = Outlet::where('status', true)->get();
        $this->data['action'] = route('tables.store');

        return view('tables.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiningTableRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        $outletId = $request->outlet_id ?: auth()->user()->current_outlet_id;
        if (!$outletId) {
            $firstOutlet = Outlet::where('status', true)->first() ?: Outlet::first();
            $outletId = $firstOutlet?->id;
        }

        if (!$outletId) {
            return redirect()->back()->withInput()->with('error', 'Outlet tidak ditemukan. Silakan buat outlet terlebih dahulu.');
        }

        $data['outlet_id'] = $outletId;

        DiningTable::create($data);

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table has been created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DiningTable $table)
    {
        $this->data['outlets'] = Outlet::where('status', true)->get();
        $this->data['table_data'] = $table;
        $this->data['action'] = route('tables.update', $table->uuid);

        return view('tables.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiningTableRequest $request, DiningTable $table)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;

        if ($request->filled('outlet_id')) {
            $data['outlet_id'] = $request->outlet_id;
        }

        $table->update($data);

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DiningTable $table)
    {
        $table->delete();

        return redirect()
            ->route('tables.index')
            ->with('success', 'Table has been deleted successfully!');
    }
}
