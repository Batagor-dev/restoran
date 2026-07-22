<?php

namespace App\Http\Controllers;

use App\DataTables\PermissionGroupDataTable;
use App\Http\Requests\StorePermissionGroupRequest;
use App\Http\Requests\UpdatePermissionGroupRequest;
use App\Models\PermissionGroup;
use Illuminate\Http\Response;

class PermissionGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(PermissionGroupDataTable $dataTable)
    {
        return $dataTable->render('permissiongroup.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['permissiongroups'] = PermissionGroup::all();

        $this->data['action'] = '/permissiongroup';

        return view('permissiongroup.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StorePermissionGroupRequest $request)
    {
        PermissionGroup::create($request->all());

        return redirect('/permissiongroup')->with('success', 'New permission group has been created!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(PermissionGroup $permissiongroup)
    {
        $this->data['permissiongroups'] = PermissionGroup::all();

        $this->data['permissiongroup_data'] = $permissiongroup;
        $this->data['action'] = '/permissiongroup/'.$permissiongroup->uuid;

        return view('permissiongroup.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(UpdatePermissionGroupRequest $request, PermissionGroup $permissiongroup)
    {
        $permissiongroup->update($request->validated());

        return redirect('/permissiongroup')->with('success', 'Permission Group has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(PermissionGroup $permissiongroup)
    {
        $permissiongroup->delete();

        return redirect('/permissiongroup')->with('success', 'Permission Group has been deleted!');
    }
}
