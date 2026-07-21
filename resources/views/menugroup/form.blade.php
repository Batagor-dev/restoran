@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Menu Group';

    if (isset($menugroup_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $menugroup_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();
@endphp

@extends('layouts.backend.main')

@section('title', 'Menu Group Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ $action }}" class="space-y-6">
                @isset($menugroup_data) @method('PUT') @endisset
                @csrf

                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-4">{{ $sub_title }}</h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Group Name -->
                        <x-ui.input 
                            name="name" 
                            label="Group Name" 
                            placeholder="e.g., UTAMA, KONTEN, PENGATURAN" 
                            value="{{ old('name', $menugroup_data->name ?? '') }}"
                            required
                        />

                        <!-- Sort Order -->
                        <x-ui.input 
                            type="number"
                            name="sort" 
                            label="Sort Order" 
                            placeholder="1" 
                            value="{{ old('sort', $menugroup_data->sort ?? '1') }}"
                            min="0"
                            required
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
                            :checked="old('status', $menugroup_data->status ?? true) ? true : false"
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
