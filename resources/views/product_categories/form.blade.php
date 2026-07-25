@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Product Category Form';

    if (isset($product_category_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $product_category_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();
@endphp

@extends('layouts.backend.main')

@section('title', 'Product Category Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ $action }}" class="space-y-6">
                @isset($product_category_data) @method('PUT') @endisset
                @csrf

                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-6">{{ $sub_title }}</h5>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Category Name -->
                        <x-ui.input 
                            name="name" 
                            label="Category Name" 
                            placeholder="Enter product category name" 
                            value="{{ old('name', $product_category_data->name ?? '') }}"
                            required
                        />

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-satoshi-medium text-slate-700 mb-2">Description</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="4" 
                                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-satoshi-medium text-slate-900 placeholder:text-slate-400 focus:border-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-900 transition-colors"
                                placeholder="Enter description (optional)"
                            >{{ old('description', $product_category_data->description ?? '') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Active -->
                        <div class="pt-2">
                            <x-ui.checkbox 
                                name="is_active" 
                                label="Active Status" 
                                :checked="old('is_active', $product_category_data->is_active ?? true)"
                                value="1"
                                :reverse="true"
                            />
                        </div>
                    </div>
                </div>

                <!-- Submit / Cancel -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ $breadcrumb_parent?->url ?? route('product_categories.index') }}'">
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

@push('scripts')
    {{-- SweetAlert Notification --}}
    @if(session('success'))
        <script>
            Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}" });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({ icon: 'error', title: 'Error', text: "{{ session('error') }}" });
        </script>
    @endif
@endpush
