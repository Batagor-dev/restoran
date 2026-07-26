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
                                placeholder="Enter promo name" 
                                value="{{ old('name', $promo_data->name ?? '') }}"
                                required
                            />
                        </div>

                        <!-- Scope -->
                        <div>
                            <x-ui.select2 
                                name="scope" 
                                label="Scope" 
                                placeholder="Select scope..."
                                :value="old('scope', $promo_data->scope ?? 'order')"
                                :options="[
                                    ['value' => 'order', 'label' => 'Order'],
                                    ['value' => 'product', 'label' => 'Product'],
                                    ['value' => 'category_product', 'label' => 'Category Product'],
                                ]"
                            />
                        </div>

                        <!-- Type -->
                        <div>
                            <x-ui.select2 
                                name="type" 
                                label="Discount Type" 
                                placeholder="Select discount type..."
                                :value="old('type', $promo_data->type ?? 'percentage')"
                                :options="[
                                    ['value' => 'percentage', 'label' => 'Percentage (%)'],
                                    ['value' => 'fixed', 'label' => 'Fixed Amount (Rp)'],
                                ]"
                            />
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
