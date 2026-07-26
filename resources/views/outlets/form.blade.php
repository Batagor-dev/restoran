@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Outlet Form';

    if (isset($outlet_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $outlet_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();
@endphp

@extends('layouts.backend.main')

@section('title', 'Outlet Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ $action }}" class="space-y-6">
                @isset($outlet_data) @method('PUT') @endisset
                @csrf

                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-4">{{ $sub_title }}</h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Outlet Name -->
                        <x-ui.input 
                            name="name" 
                            label="Outlet Name" 
                            placeholder="e.g., Outlet Bandung" 
                            value="{{ old('name', $outlet_data->name ?? '') }}"
                            required
                        />

                        <!-- Phone -->
                        <x-ui.input 
                            type="text"
                            name="phone" 
                            label="Phone Number" 
                            placeholder="e.g., 022-123456" 
                            value="{{ old('phone', $outlet_data->phone ?? '') }}"
                        />
                    </div>

                    <!-- Address -->
                    <div class="mt-6">
                        <label for="address" class="mb-2 block text-base font-satoshi-medium text-slate-700">Address</label>
                        <textarea 
                            id="address"
                            name="address" 
                            rows="3"
                            placeholder="e.g., Jl. Braga No. 10, Bandung"
                            class="block w-full font-satoshi-medium rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base outline-none transition focus:bg-white focus:ring-2 focus:border-slate-400 focus:ring-slate-200"
                        >{{ old('address', $outlet_data->address ?? '') }}</textarea>
                        @error('address')
                            <span class="mt-1.5 block text-sm font-medium text-red-600">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Status Switch -->
                    <div class="mt-6">
                        <input type="hidden" name="status" value="0">
                        <x-ui.switch 
                            name="status" 
                            label="Status" 
                            value="1"
                            :checked="old('status', $outlet_data->status ?? true) ? true : false"
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
