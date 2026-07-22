<?php

namespace App\Http\Controllers;

use App\DataTables\OutletDataTable;
use App\Http\Requests\StoreOutletRequest;
use App\Http\Requests\UpdateOutletRequest;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OutletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(OutletDataTable $dataTable)
    {

        return $dataTable->render('outlets.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->data['action'] = '/outlet';

        return view('outlets.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOutletRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $request->has('status') ? (bool) $request->status : true;

        Outlet::create($data);

        return redirect('/outlet')->with('success', 'New outlet has been created!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Outlet $outlet)
    {
        $this->data['outlet_data'] = $outlet;
        $this->data['action'] = '/outlet/'.$outlet->uuid;

        return view('outlets.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOutletRequest $request, Outlet $outlet)
    {

        $data = $request->validated();
        $data['status'] = $request->has('status') ? (bool) $request->status : false;

        $outlet->update($data);

        return redirect('/outlet')->with('success', 'Outlet has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Outlet $outlet)
    {

        $outlet->delete();

        return redirect('/outlet')->with('success', 'Outlet has been deleted!');
    }

    /**
     * Switch current active outlet.
     */
    public function switchOutlet(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->back();
        }

        $outletId = $request->input('outlet_id');

        if ($outletId === 'all') {
            // Only Super Admin or Owner can set current_outlet_id to null
            if ($user->hasRole(['Super Admin', 'Owner'])) {
                $user->update(['current_outlet_id' => null]);

                return redirect()->back()->with('success', 'Switched to All Outlets.');
            }

            return redirect()->back()->with('error', 'Unauthorized to access all outlets.');
        }

        // Find the outlet by ID
        $outlet = Outlet::find($outletId);
        if (! $outlet) {
            return redirect()->back()->with('error', 'Outlet not found.');
        }

        // Check permission
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            $user->update(['current_outlet_id' => $outlet->id]);

            return redirect()->back()->with('success', "Switched to {$outlet->name}.");
        }

        // Standard user / Employee must be assigned to the outlet
        $hasAccess = $user->outlets()->where('outlets.id', $outlet->id)->exists();
        if ($hasAccess) {
            $user->update(['current_outlet_id' => $outlet->id]);

            return redirect()->back()->with('success', "Switched to {$outlet->name}.");
        }

        return redirect()->back()->with('error', 'You do not have access to this outlet.');
    }
}
