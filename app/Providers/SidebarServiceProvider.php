<?php

namespace App\Providers;

use App\Models\Menu;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SidebarServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer(['components.layout.admin.sidebar', 'layout.backend.sidebar'], function ($view) {
            $menus = Menu::whereNull('menu_id')
                ->where('status', 1)
                ->with(['children', 'menuGroup'])
                ->get();

            $groupedMenus = $menus->groupBy(function($menu) {
                return $menu->menu_group_id ?? 0;
            })->sortBy(function($items, $groupId) {
                if ($groupId == 0) return 9999;
                return $items->first()?->menuGroup?->sort ?? 999;
            });

            $view->with('groupedMenus', $groupedMenus);
            $view->with('menus', $menus);
        });
    }

    public function register()
    {
        //
    }
}