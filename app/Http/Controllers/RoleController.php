<?php

namespace App\Http\Controllers;

use App\DataTables\RoleDataTable;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(RoleDataTable $dataTable)
    {
        return $dataTable->render('role.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['action'] = '/role';

        return view('role.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StoreRoleRequest $request)
    {
        Role::create($request->all());

        return redirect('/role')->with('success', 'New role has been created!');
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Role $role)
    {
        $this->data['action'] = '/role/showaction/'.$role->uuid;
        $this->data['permission_groups'] = PermissionGroup::with(['permissions', 'menuGroups'])->get(); // eager-load
        $this->data['permissions'] = Permission::whereNull('permission_group_id')->get();
        $this->data['role'] = $role->load('permissions'); // hak akses yg sudah dimiliki

        return view('role.permission', $this->data);
    }

    public function showaction(Request $request, Role $role)
    {
        /* 1. ambil daftar yg dicentang, buang elemen kosong */
        $permission_array = array_filter(explode(',', $request->input('permission', '')));

        /* 2. hapus permission yg tidak ada di daftar baru */
        foreach ($role->permissions as $perm) {
            if (! in_array($perm->id, $permission_array)) {
                $role->revokePermissionTo($perm);   // $perm sudah model, tak perlu find lagi
            }
        }

        /* 3. berikan permission baru */
        foreach ($permission_array as $id) {
            $permission = Permission::find($id);
            if ($permission) {                     // pastikan ditemukan
                $role->givePermissionTo($permission->name);
            }
        }

        return redirect('/role')->with('success', 'Permission has been updated!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Role $role)
    {
        $this->data['role_data'] = $role;
        $this->data['action'] = '/role/'.$role->uuid;

        return view('role.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update($request->validated());

        return redirect('/role')->with('success', 'Role has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect('/role')->with('success', 'Role has been deleted!');
    }
}
