@php
    $isEdit = isset($customerPromo);
    $title = $isEdit ? 'Edit Customer Promo' : 'Add Customer Promo';
@endphp

@extends('layouts.backend.main')

@section('title', $title)

@section('content')
<div class="space-y-8 pb-12">
    <x-ui.card>
        <div class="flex items-center justify-between mb-6">
            <h5 class="text-lg font-satoshi-bold text-slate-900 mb-0">{{ $title }}</h5>
        </div>

        <form action="{{ $action }}" method="POST" class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <div>
                    <x-ui.input
                        name="name"
                        id="name"
                        label="Promo Name"
                        :value="old('name', $customerPromo->name ?? '')"
                        placeholder="Enter promo name"
                        :required="true"
                        :error="$errors->first('name')"
                    />
                </div>

                {{-- Scope --}}
                <div>
                    <x-ui.select2
                        name="scope"
                        id="scope"
                        label="Scope"
                        :required="true"
                        :options="[
                            'order' => 'Order',
                            'product' => 'Product',
                            'category_product' => 'Category Product'
                        ]"
                        :selected="old('scope', $customerPromo->scope ?? '')"
                        :error="$errors->first('scope')"
                    />
                </div>

                {{-- Type --}}
                <div>
                    <x-ui.select2
                        name="type"
                        id="type"
                        label="Discount Type"
                        :required="true"
                        :options="[
                            'percentage' => 'Percentage (%)',
                            'fixed' => 'Fixed (Rp)'
                        ]"
                        :selected="old('type', $customerPromo->type ?? '')"
                        :error="$errors->first('type')"
                    />
                </div>

                {{-- Discount Value --}}
                <div>
                    <x-ui.input
                        name="discount_value"
                        id="discount_value"
                        label="Discount Value"
                        type="number"
                        :value="old('discount_value', $customerPromo->discount_value ?? 0)"
                        placeholder="Enter discount value"
                        :required="true"
                        min="0"
                        step="0.01"
                        :error="$errors->first('discount_value')"
                    />
                </div>

                {{-- Minimum Purchase --}}
                <div>
                    <x-ui.input
                        name="minimum_purchase"
                        id="minimum_purchase"
                        label="Minimum Purchase (Rp)"
                        type="number"
                        :value="old('minimum_purchase', $customerPromo->minimum_purchase ?? '')"
                        placeholder="Enter minimum purchase (optional)"
                        min="0"
                        step="0.01"
                        :error="$errors->first('minimum_purchase')"
                    />
                </div>

                {{-- Start Date --}}
                <div>
                    <x-ui.date
                        name="start_date"
                        id="start_date"
                        label="Start Date"
                        :value="old('start_date', $customerPromo->start_date ?? '')"
                        :required="true"
                        :error="$errors->first('start_date')"
                    />
                </div>

                {{-- End Date --}}
                <div>
                    <x-ui.date
                        name="end_date"
                        id="end_date"
                        label="End Date"
                        :value="old('end_date', $customerPromo->end_date ?? '')"
                        :required="true"
                        :error="$errors->first('end_date')"
                    />
                </div>

                {{-- Usage Limit --}}
                <div>
                    <x-ui.input
                        name="usage_limit"
                        id="usage_limit"
                        label="Usage Limit (Total)"
                        type="number"
                        :value="old('usage_limit', $customerPromo->usage_limit ?? '')"
                        placeholder="Max total usages (optional)"
                        min="0"
                        :error="$errors->first('usage_limit')"
                    />
                </div>

                {{-- Usage Per Customer --}}
                <div>
                    <x-ui.input
                        name="usage_per_customer"
                        id="usage_per_customer"
                        label="Usage Per Customer"
                        type="number"
                        :value="old('usage_per_customer', $customerPromo->usage_per_customer ?? '')"
                        placeholder="Max usage per customer (optional)"
                        min="0"
                        :error="$errors->first('usage_per_customer')"
                    />
                </div>

                {{-- Description (Full Width) --}}
                <div class="md:col-span-2">
                    <x-ui.textarea
                        name="description"
                        id="description"
                        label="Description"
                        :value="old('description', $customerPromo->description ?? '')"
                        placeholder="Enter promo description (optional)"
                        rows="3"
                        :error="$errors->first('description')"
                    />
                </div>

                {{-- Is Active --}}
                <div>
                    <x-ui.checkbox
                        name="is_active"
                        id="is_active"
                        label="Active"
                        :checked="old('is_active', $customerPromo->is_active ?? true)"
                        value="1"
                    />
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <x-ui.button type="button" size="sm" style="secondary" onclick="window.location.href='{{ route('customer-promos.index') }}'">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" size="sm">
                    <i></i> Submit
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection