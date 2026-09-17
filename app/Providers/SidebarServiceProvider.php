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
                ->with([
                    'permissionGroup',
                    'menuGroup.permissionGroup',
                    'children.permissionGroup',
                    'children.children.permissionGroup',
                ])
                ->orderBy('sort')
                ->get();

            $groupedMenus = $menus->groupBy(function ($menu) {
                return $menu->menu_group_id ?? 0;
            })->sortBy(function ($items, $groupId) {
                if ($groupId == 0) {
                    return 9999;
                }

                return $items->first()?->menuGroup?->sort ?? 999;
            });

            $view->with('groupedMenus', $groupedMenus);
            $view->with('menus', $menus);
        });

        View::composer('components.layout.admin.header', function ($view) {
            $user = auth()->user();
            if (! $user) {
                $view->with([
                    'userOutlets' => collect(),
                    'selectedValue' => 'all',
                    'isSuperOrOwner' => false,
                    'userRoleName' => 'User',
                ]);

                return;
            }

            $isSuperOrOwner = $user->hasRole(['Super Admin', 'Owner']);
            if ($isSuperOrOwner) {
                $userOutlets = \Illuminate\Support\Facades\Cache::remember('active_outlets_list', 60, function () {
                    return \App\Models\Outlet::where('status', true)->orderBy('name')->get();
                });
            } else {
                $userOutlets = $user->relationLoaded('outlets') ? $user->outlets : $user->outlets()->get();
            }

            $view->with([
                'userOutlets' => $userOutlets,
                'selectedValue' => $user->current_outlet_id ?? 'all',
                'isSuperOrOwner' => $isSuperOrOwner,
                'userRoleName' => $user->getRoleNames()->first() ?? 'User',
            ]);
        });

        View::composer('layouts.backend.main', function ($view) {
            $view->with('appSettings', settings());
        });
    }

    public function register()
    {
        //
    }
}
