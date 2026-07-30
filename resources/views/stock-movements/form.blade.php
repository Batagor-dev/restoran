@php
    $isEdit = isset($stockMovement);
    $title = $isEdit ? 'Edit Stock Movement' : 'Add Stock Movement';
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
                {{-- Product Stock --}}
                <div>
                    <x-ui.select2
                        name="product_stock_id"
                        id="product_stock_id"
                        label="Product Stock"
                        :required="true"
                        :options="$stocks"
                        option-value="id"
                        option-label="product_name"
                        :selected="old('product_stock_id', $stockMovement->product_stock_id ?? '')"
                        placeholder="Select Product Stock"
                        :error="$errors->first('product_stock_id')"
                    />
                </div>

                {{-- Movement Type --}}
                <div>
                    <x-ui.select2
                        name="movement_type"
                        id="movement_type"
                        label="Movement Type"
                        :required="true"
                        :options="['in' => 'Stock In', 'out' => 'Stock Out', 'adjustment' => 'Adjustment', 'return' => 'Return']"
                        option-value="key"
                        option-label="value"
                        :selected="old('movement_type', $stockMovement->movement_type ?? '')"
                        placeholder="Select Movement Type"
                        :error="$errors->first('movement_type')"
                    />
                </div>

                {{-- Quantity --}}
                <div>
                    <x-ui.input
                        name="quantity"
                        id="quantity"
                        label="Quantity"
                        type="number"
                        :value="old('quantity', $stockMovement->quantity ?? 1)"
                        placeholder="Enter quantity"
                        :required="true"
                        min="1"
                        :error="$errors->first('quantity')"
                    />
                </div>

                {{-- Reference Type --}}
                <div>
                    <x-ui.input
                        name="reference_type"
                        id="reference_type"
                        label="Reference Type"
                        :value="old('reference_type', $stockMovement->reference_type ?? '')"
                        placeholder="e.g. purchase, sale, return"
                        :error="$errors->first('reference_type')"
                    />
                </div>

                {{-- Notes --}}
                <div class="md:col-span-2">
                    <x-ui.textarea
                        name="notes"
                        id="notes"
                        label="Notes"
                        :value="old('notes', $stockMovement->notes ?? '')"
                        placeholder="Enter notes (optional)"
                        rows="3"
                        :error="$errors->first('notes')"
                    />
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <x-ui.button type="submit" size="sm">
                    <i class="ri-save-line mr-1"></i> Save
                </x-ui.button>
                <x-ui.button type="button" size="sm" style="secondary" onclick="window.location.href='{{ route('stock-movements.index') }}'">
                    Cancel
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection