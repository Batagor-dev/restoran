@php
    $product_data = $product_data ?? $product ?? null;
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Product Form';

    if (isset($product_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $product_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();
@endphp

@extends('layouts.backend.main')

@section('title', 'Product Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
                @isset($product_data) @method('PUT') @endisset
                @csrf

                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-6">{{ $sub_title }}</h5>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Product Name -->
                        <div>
                            <x-ui.input 
                                name="name" 
                                label="Product Name" 
                                placeholder="Enter product name" 
                                value="{{ old('name', $product_data->name ?? '') }}"
                                required
                            />
                        </div>

                        <!-- Category -->
                        <div>
                            <x-ui.select2 
                                name="category_id" 
                                label="Category"
                                placeholder="Select category..."
                                :value="old('category_id', $product_data->category_id ?? '')"
                                :options="$categories->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()"
                            />
                        </div>

                        <!-- Price -->
                        <div>
                            <x-ui.input 
                                type="text"
                                name="price" 
                                label="Price (Rp)" 
                                placeholder="e.g. 25.000" 
                                value="{{ old('price', isset($product_data->price) ? number_format($product_data->price, 0, ',', '.') : '') }}"
                                class="currency-format"
                                required
                            />
                        </div>

                        <!-- Active Status -->
                        <div>
                            <x-ui.switch 
                                name="is_active" 
                                label="Active Status" 
                                :checked="old('is_active', $product_data->is_active ?? true)"
                                value="1"
                            />
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-satoshi-medium text-slate-700 mb-2">Description</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="3" 
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base font-satoshi-medium text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all"
                                placeholder="Enter product description (optional)"
                            >{{ old('description', $product_data->description ?? '') }}</textarea>
                            @error('description')
                                <p class="mt-1.5 block text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cover Image -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-satoshi-medium text-slate-700 mb-2">Cover Image</label>
                            <x-ui.dropzone 
                                name="image" 
                                accept="image/*"
                                :previewUrl="isset($product_data->image) ? asset('storage/'.$product_data->image) : null"
                                :required="!isset($product_data)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Submit / Cancel -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ $breadcrumb_parent?->url ?? route('products.index') }}'">
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
    {{-- Live thousand dot currency formatting --}}
    <script>
    $(document).on('input', '.currency-format', function () {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value) {
            $(this).val(new Intl.NumberFormat('id-ID').format(value));
        } else {
            $(this).val('');
        }
    });
    </script>

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