@php
    $breadcrumbsData = Breadcrumbs::generate(); 
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Dashboard';
@endphp

@extends('layouts.backend.main')

@section('title', 'Outlet Management')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
<div class="space-y-8 pb-12">
    <x-ui.card>
        <div class="flex items-center justify-between mb-4">
            <h5 class="mb-0">{{$sub_title}}</h5>
            @can('Outlet Create')
                <x-ui.button href="{{ route('outlet.create') }}" color="primary" size="sm">
                    <i class="ri-add-line mr-1"></i> Add Outlet
                </x-ui.button>
            @endcan
        </div>
        <div>
            {{ $dataTable->table(['width' => '100%']) }}
        </div>
    </x-ui.card>    
</div>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
