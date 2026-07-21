<?php

namespace App\Http\Controllers;

use App\Models\MenuGroup;
use App\Http\Requests\StoreMenuGroupRequest;
use App\Http\Requests\UpdateMenuGroupRequest;
use App\DataTables\MenuGroupDataTable;

class MenuGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(MenuGroupDataTable $dataTable)
    {
        return $dataTable->render('menugroup.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['menugroups'] = MenuGroup::all();
        $this->data['action'] = "/menugroup";
        return view('menugroup.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMenuGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMenuGroupRequest $request)
    {
        MenuGroup::create($request->validated());

        return redirect('/menugroup')->with('success', 'New menu group has been created!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MenuGroup  $menugroup
     * @return \Illuminate\Http\Response
     */
    public function edit(MenuGroup $menugroup)
    {
        $this->data['menugroups'] = MenuGroup::all();
        $this->data['menugroup_data'] = $menugroup;
        $this->data['action'] = "/menugroup/".$menugroup->uuid;
        return view('menugroup.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMenuGroupRequest  $request
     * @param  \App\Models\MenuGroup  $menugroup
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMenuGroupRequest $request, MenuGroup $menugroup)
    {
        $menugroup->update($request->validated());

        return redirect('/menugroup')->with('success', 'Menu group has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MenuGroup  $menugroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(MenuGroup $menugroup)
    {
        $menugroup->delete();
        return redirect('/menugroup')->with('success', 'Menu group has been deleted!');
    }
}
