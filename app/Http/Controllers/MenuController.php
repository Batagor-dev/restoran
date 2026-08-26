<?php

namespace App\Http\Controllers;

use App\DataTables\MenuDataTable;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\PermissionGroup;
use Illuminate\Http\Response;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(MenuDataTable $dataTable)
    {
        return $dataTable->render('menu.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['menus'] = Menu::all();
        $this->data['menugroups'] = MenuGroup::where('status', 1)->orderBy('sort')->get();
        $this->data['permissiongroups'] = PermissionGroup::all();
        $this->data['action'] = '/menu';

        return view('menu.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StoreMenuRequest $request)
    {
        Menu::create($request->all());

        return redirect('/menu')->with('success', 'New menu has been created!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Menu $menu)
    {
        $this->data['menus'] = Menu::all();
        $this->data['menugroups'] = MenuGroup::where('status', 1)->orderBy('sort')->get();
        $this->data['permissiongroups'] = PermissionGroup::all();
        $this->data['menu_data'] = $menu;
        $this->data['action'] = '/menu/'.$menu->uuid;

        return view('menu.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        $menu->update($request->all());

        return redirect('/menu')->with('success', 'Menu has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect('/menu')->with('success', 'Menu has been deleted!');
    }
}
