<?php

namespace App\Http\Controllers;

use App\DataTables\MenuGroupDataTable;
use App\Http\Requests\StoreMenuGroupRequest;
use App\Http\Requests\UpdateMenuGroupRequest;
use App\Models\MenuGroup;
use App\Models\PermissionGroup;
use Illuminate\Http\Response;

class MenuGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(MenuGroupDataTable $dataTable)
    {
        return $dataTable->render('menugroup.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['menugroups'] = MenuGroup::all();
        $this->data['permission_groups'] = PermissionGroup::all();
        $this->data['action'] = '/menugroup';

        return view('menugroup.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StoreMenuGroupRequest $request)
    {
        MenuGroup::create($request->validated());

        return redirect('/menugroup')->with('success', 'New menu group has been created!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(MenuGroup $menugroup)
    {
        $this->data['menugroups'] = MenuGroup::all();
        $this->data['permission_groups'] = PermissionGroup::all();
        $this->data['menugroup_data'] = $menugroup;
        $this->data['action'] = '/menugroup/'.$menugroup->uuid;

        return view('menugroup.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(UpdateMenuGroupRequest $request, MenuGroup $menugroup)
    {
        $menugroup->update($request->validated());

        return redirect('/menugroup')->with('success', 'Menu group has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(MenuGroup $menugroup)
    {
        $menugroup->delete();

        return redirect('/menugroup')->with('success', 'Menu group has been deleted!');
    }
}
