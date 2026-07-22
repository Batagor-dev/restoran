<?php

namespace App\Http\Controllers;

use App\DataTables\PermissionDataTable;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Http\Response;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(PermissionDataTable $dataTable)
    {
        return $dataTable->render('permission.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['permissiongroups'] = PermissionGroup::all();

        $this->data['action'] = '/permission';

        return view('permission.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StorePermissionRequest $request)
    {
        Permission::create($request->all());

        return redirect('/permission')->with('success', 'New permission has been created!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Permission $permission)
    {
        $this->data['permissiongroups'] = PermissionGroup::all();

        $this->data['permission_data'] = $permission;
        $this->data['action'] = '/permission/'.$permission->uuid;

        return view('permission.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $permission->update($request->validated());

        return redirect('/permission')->with('success', 'Permission has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect('/permission')->with('success', 'Permission has been deleted!');
    }
}
