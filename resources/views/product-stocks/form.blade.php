@php
    $isEdit = isset($productStock);
    $title = $isEdit ? 'Edit Product Stock' : 'Add Product Stock';
@endphp

@extends('layouts.backend.main')

@section('title', $title)

@section('content')
<div class="space-y-8 pb-12">
    <x-ui.card>
        <div class="flex items-center justify-between mb-6">
            <h5 class="text-lg font-satoshi-bold text-slate-900 mb-0">{{ $title }}</h5>
        </div>

        <form action="{{ $action ?? route('product-stocks.store') }}" method="POST" class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(!$isEdit)
                    {{-- Product --}}
                    <div>
                        <x-ui.select2
                            name="product_id"
                            id="product_id"
                            label="Product"
                            :required="true"
                            :options="$products ?? []"
                            option-value="id"
                            option-label="name"
                            :selected="old('product_id', $productStock->product_id ?? '')"
                            placeholder="Select Product"
                            :error="$errors->first('product_id')"
                        />
                    </div>

                    {{-- Outlet --}}
                    <div>
                        <x-ui.select2
                            name="outlet_id"
                            id="outlet_id"
                            label="Outlet"
                            :required="true"
                            :options="$outlets ?? []"
                            option-value="id"
                            option-label="name"
                            :selected="old('outlet_id', $productStock->outlet_id ?? '')"
                            placeholder="Select Outlet"
                            :error="$errors->first('outlet_id')"
                        />
                    </div>
                @endif

                {{-- Quantity --}}
                <div>
                    <x-ui.input
                        name="quantity"
                        id="quantity"
                        label="Quantity"
                        type="number"
                        :value="old('quantity', $productStock->quantity ?? 0)"
                        placeholder="Enter quantity"
                        :required="true"
                        min="0"
                        :error="$errors->first('quantity')"
                    />
                </div>
            </div>

            @if($isEdit)
                <div class="bg-slate-50 rounded-lg p-4 text-sm text-slate-600 border border-slate-200">
                    <p><strong>Product:</strong> {{ $productStock->product->name ?? '-' }}</p>
                    <p><strong>Outlet:</strong> {{ $productStock->outlet->name ?? '-' }}</p>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <x-ui.button type="button" size="sm" style="secondary" onclick="window.location.href='{{ route('product-stocks.index') }}'">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" size="sm">
                    <i></i> Sumbit
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection