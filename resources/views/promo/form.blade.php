@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Promo Form';

    if (isset($promo_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $promo_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();
@endphp

@extends('layouts.backend.main')

@section('title', 'Promo Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ $action }}" class="space-y-6">
                @isset($promo_data) @method('PUT') @endisset
                @csrf

                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-6">{{ $sub_title }}</h5>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Promo Name -->
                        <div class="md:col-span-2">
                            <x-ui.input
                                name="name"
                                label="Promo Name"
                                placeholder="Enter promo name (also used as promo code)"
                                value="{{ old('name', $promo_data->name ?? '') }}"
                                required
                            />
                        </div>

                        <!-- Scope -->
                        <div>
                            <label for="scope" class="mb-2 block text-base font-satoshi-medium text-slate-700">Scope</label>
                            <select id="scope-select" name="scope" class="promo-select2 w-full">
                                <option value="order" {{ old('scope', $promo_data->scope ?? 'order') === 'order' ? 'selected' : '' }}>Order</option>
                                <option value="product" {{ old('scope', $promo_data->scope ?? 'order') === 'product' ? 'selected' : '' }}>Product</option>
                                <option value="category_product" {{ old('scope', $promo_data->scope ?? 'order') === 'category_product' ? 'selected' : '' }}>Category Product</option>
                            </select>
                            @error('scope')
                                <p class="mt-1.5 block text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div>
                            <label for="type" class="mb-2 block text-base font-satoshi-medium text-slate-700">Discount Type</label>
                            <select id="type" name="type" class="promo-select2 w-full">
                                <option value="percentage" {{ old('type', $promo_data->type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('type', $promo_data->type ?? 'percentage') === 'fixed' ? 'selected' : '' }}>Fixed Amount (Rp)</option>
                            </select>
                        </div>

                        <!-- Discount Value -->
                        <div>
                            <x-ui.input 
                                type="text" 
                                name="discount_value" 
                                label="Discount Value" 
                                placeholder="e.g. 10 or 15.000" 
                                value="{{ old('discount_value', isset($promo_data->discount_value) ? number_format($promo_data->discount_value, 0, ',', '.') : '') }}"
                                class="currency-format"
                                required
                            />
                        </div>

                        <!-- Minimum Purchase -->
                        <div>
                            <x-ui.input 
                                type="text" 
                                name="minimum_purchase" 
                                label="Minimum Purchase (Rp)" 
                                placeholder="Optional (e.g. 50.000)" 
                                value="{{ old('minimum_purchase', isset($promo_data->minimum_purchase) ? number_format($promo_data->minimum_purchase, 0, ',', '.') : '') }}"
                                class="currency-format"
                            />
                        </div>

                        <!-- Start Date -->
                        <div>
                            <x-ui.date 
                                name="start_date" 
                                label="Start Date & Time" 
                                type="datetime"
                                placeholder="Select start date & time..."
                                value="{{ old('start_date', isset($promo_data->start_date) ? $promo_data->start_date->format('Y-m-d H:i') : '') }}"
                            />
                        </div>

                        <!-- End Date -->
                        <div>
                            <x-ui.date 
                                name="end_date" 
                                label="End Date & Time" 
                                type="datetime"
                                placeholder="Select end date & time..."
                                value="{{ old('end_date', isset($promo_data->end_date) ? $promo_data->end_date->format('Y-m-d H:i') : '') }}"
                            />
                        </div>

                        <!-- Usage Limit -->
                        <div>
                            <x-ui.input 
                                type="number" 
                                name="usage_limit" 
                                label="Total Usage Limit" 
                                placeholder="Max total usages allowed (Optional)" 
                                value="{{ old('usage_limit', $promo_data->usage_limit ?? '') }}"
                            />
                        </div>

                        <!-- Usage Limit Per Customer -->
                        <div>
                            <x-ui.input 
                                type="number" 
                                name="usage_per_customer" 
                                label="Usage Limit Per Customer" 
                                placeholder="Max usage per customer (Optional)" 
                                value="{{ old('usage_per_customer', $promo_data->usage_per_customer ?? '') }}"
                            />
                        </div>

                        <!-- Products (scope = product) -->
                        <div class="md:col-span-2" id="products-wrapper" style="display: none;">
                            <label class="mb-2 block text-base font-satoshi-medium text-slate-700">Eligible Products</label>
                            <select id="promo-products" name="products[]" class="promo-select2 w-full" multiple>
                                @foreach($products as $product)
                                    <option value="{{ $product['value'] }}"
                                        {{ in_array($product['value'], (array) ($selected_products ?? old('products', []))) ? 'selected' : '' }}>
                                        {{ $product['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('products')
                                <p class="mt-1.5 block text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Categories (scope = category_product) -->
                        <div class="md:col-span-2" id="categories-wrapper" style="display: none;">
                            <label class="mb-2 block text-base font-satoshi-medium text-slate-700">Eligible Product Categories</label>
                            <select id="promo-categories" name="categories[]" class="promo-select2 w-full" multiple>
                                @foreach($categories as $category)
                                    <option value="{{ $category['value'] }}"
                                        {{ in_array($category['value'], (array) ($selected_categories ?? old('categories', []))) ? 'selected' : '' }}>
                                        {{ $category['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categories')
                                <p class="mt-1.5 block text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-satoshi-medium text-slate-700 mb-2">Description</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="3" 
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base font-satoshi-medium text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all"
                                placeholder="Enter promo terms or description (optional)"
                            >{{ old('description', $promo_data->description ?? '') }}</textarea>
                            @error('description')
                                <p class="mt-1.5 block text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Active -->
                        <div class="md:col-span-2">
                            <x-ui.switch 
                                name="is_active" 
                                label="Active Status" 
                                :checked="old('is_active', $promo_data->is_active ?? true)"
                                value="1"
                            />
                        </div>
                    </div>
                </div>

                <!-- Submit / Cancel -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ $breadcrumb_parent?->url ?? route('promo.index') }}'">
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
    {{-- Select2 (pola sama dengan POS) + toggle berdasarkan scope --}}
    <script>
    $(document).ready(function () {
        $('.promo-select2').select2({
            width: '100%',
            placeholder: 'Select...',
            allowClear: false
        });

        function toggleScopeFields() {
            var scope = $('#scope-select').val();

            $('#products-wrapper').toggle(scope === 'product');
            $('#categories-wrapper').toggle(scope === 'category_product');
        }

        $('#scope-select').on('change', toggleScopeFields);
        toggleScopeFields();
    });
    </script>

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
@endpush
