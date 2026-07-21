@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Menu Group';
    $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
@endphp

@extends('layouts.backend.main')

@section('title', 'Menu Groups')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
<div class="space-y-8 pb-12">
    <x-ui.card>
        <div class="flex items-center justify-between mb-4">
            <h5 class="text-lg font-satoshi-bold text-slate-900 mb-0">{{$sub_title}}</h5>
            <x-ui.button href="{{ route('menugroup.create') }}" color="primary" size="sm">
                <i class="ri-add-line mr-1"></i> Add Menu Group
            </x-ui.button>
        </div>
        <div>
            {{ $dataTable->table(['width' => '100%']) }}
        </div>
    </x-ui.card>    
</div>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    @if(session('success'))
        <script>
            Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}" });
        </script>
    @endif
    @if(session('error'))
        <script>
            Swal.fire({ icon: 'error',   title: 'Error',  text: "{{ session('error') }}" });
        </script>
    @endif
    <script>
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        let form = $(this).closest('form');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
    </script>
@endpush
