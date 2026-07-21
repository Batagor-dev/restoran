@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Menu';

    if (isset($menu_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $menu_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();
@endphp

@extends('layouts.backend.main')

@section('title', 'Menu Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ $action }}" class="space-y-6">
                @isset($menu_data) @method('PUT') @endisset
                @csrf

                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-4">{{ $sub_title }}</h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Menu Name -->
                        <x-ui.input 
                            name="nama_menu" 
                            label="Menu Name" 
                            placeholder="Menu Display Name" 
                            value="{{ old('nama_menu', $menu_data->nama_menu ?? '') }}"
                            required
                        />

                        <!-- Route / Href -->
                        <x-ui.input 
                            name="href" 
                            label="Route / Href" 
                            placeholder="/dashboard or route name" 
                            value="{{ old('href', $menu_data->href ?? '') }}"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <!-- Icon -->
                        <div>
                            <x-ui.input 
                                name="icon" 
                                label="Icon" 
                                placeholder="ri-home-line" 
                                value="{{ old('icon', $menu_data->icon ?? '') }}"
                            />
                            <p class="mt-1 text-sm text-slate-400 font-satoshi-medium">
                                Refer to <a href="https://remixicon.com" target="_blank" class="text-black underline font-satoshi-medium">RemixIcon</a>
                            </p>
                        </div>

                        <!-- Sort Order -->
                        <x-ui.input 
                            type="number"
                            name="sort" 
                            label="Sort Order" 
                            placeholder="0" 
                            value="{{ old('sort', $menu_data->sort ?? '0') }}"
                            min="0"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <!-- Menu Group -->
                        <x-ui.select2 
                            name="menu_group_id" 
                            label="Menu Group" 
                            placeholder="-- Select Group --" 
                            :options="['' => '-- No Group (Kosong) --'] + (isset($menugroups) ? $menugroups->pluck('name', 'id')->toArray() : [])"
                            :value="old('menu_group_id', $menu_data->menu_group_id ?? '')"
                        />

                        <!-- Parent Menu -->
                        <x-ui.select2 
                            name="menu_id" 
                            label="Parent Menu" 
                            placeholder="-- No Parent (Root Menu) --" 
                            :options="['' => '-- No Parent (Root Menu) --'] + $menus->reject(fn($m) => isset($menu_data) && $m->id === $menu_data->id)->pluck('nama_menu', 'id')->toArray()"
                            :value="old('menu_id', $menu_data->menu_id ?? '')"
                        />

                        <!-- Permission Group -->
                        <x-ui.select2 
                            name="permission_group_id" 
                            label="Permission Group" 
                            placeholder="-- No Permission --" 
                            :options="['' => '-- No Permission (Kosong) --'] + $permissiongroups->pluck('name', 'id')->toArray()"
                            :value="old('permission_group_id', $menu_data->permission_group_id ?? '')"
                        />
                    </div>

                    <!-- Status Switch / Checkbox -->
                    <div class="mt-6">
                        <label class="mb-2 block text-base font-satoshi-medium text-slate-700">Status</label>
                        <input type="hidden" name="status" value="0">
                        <x-ui.checkbox 
                            name="status" 
                            label="Active" 
                            value="1"
                            :checked="old('status', $menu_data->status ?? true) ? true : false"
                        />
                    </div>
                </div>

                <!-- Submit / Cancel -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ $breadcrumb_parent->url }}'">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" font="bold" size="sm">
                        Submit
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection