@php
$isEdit = isset($order);
$title = $isEdit ? 'Edit Order' : 'Add Order';
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
                {{-- Customer Name --}}
                <div>
                    <x-ui.input
                        name="customer_name"
                        id="customer_name"
                        label="Customer Name"
                        :value="old('customer_name', $order->customer_name ?? '')"
                        placeholder="Enter customer name (optional)"
                        :error="$errors->first('customer_name')" />
                </div>

                {{-- Table --}}
                <div>
                    <x-ui.select2
                        name="table_id"
                        id="table_id"
                        label="Table"
                        :options="$tables->map(function($table) {
        return ['value' => $table->id, 'label' => $table->number_table];
    })->toArray()"
                        option-value="value"
                        option-label="label"
                        :selected="old('table_id', $order->table_id ?? '')"
                        placeholder="Select Table"
                        :error="$errors->first('table_id')" />
                </div>

                {{-- Payment Method --}}
                <div>
                    <x-ui.select2
                        name="payment_method"
                        id="payment_method"
                        label="Payment Method"
                        :required="true"
                        :options="[
                            ['value' => 'cash', 'label' => 'Cash'],
                            ['value' => 'qris', 'label' => 'QRIS'],
                            ['value' => 'debit', 'label' => 'Debit Card'],
                            ['value' => 'credit', 'label' => 'Credit Card']
                        ]"
                        option-value="value"
                        option-label="label"
                        :selected="old('payment_method', $order->payment_method ?? 'cash')"
                        placeholder="Select Payment Method"
                        :error="$errors->first('payment_method')" />
                </div>

                {{-- Status Order --}}
                <div>
                    <x-ui.select2
                        name="status_order"
                        id="status_order"
                        label="Status Order"
                        :required="true"
                        :options="[
                            ['value' => 'pending', 'label' => 'Pending'],
                            ['value' => 'processing', 'label' => 'Processing'],
                            ['value' => 'completed', 'label' => 'Completed'],
                            ['value' => 'cancelled', 'label' => 'Cancelled']
                        ]"
                        option-value="value"
                        option-label="label"
                        :selected="old('status_order', $order->status_order ?? 'pending')"
                        placeholder="Select Status"
                        :error="$errors->first('status_order')" />
                </div>
            </div>

            {{-- Order Items --}}
            <div class="mt-6">
                <h6 class="text-md font-satoshi-bold text-slate-900 mb-4">Order Items</h6>

                <div id="order-items-container">
                    @if($isEdit && isset($order->items) && $order->items->count() > 0)
                    @foreach($order->items as $index => $item)
                    <div class="order-item-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 p-4 border border-slate-200 rounded-lg">
                        <div>
                            <x-ui.select2
                                name="items[{{ $index }}][product_id]"
                                :id="'items_' . $index . '_product_id'"
                                label="Product"
                                :required="true"
                                :options="$products->map(function($product) {
                                            return ['value' => $product->id, 'label' => $product->name . ' (Rp ' . number_format($product->price, 0, ',', '.') . ')'];
                                        })->toArray()"
                                option-value="value"
                                option-label="label"
                                :selected="old('items.' . $index . '.product_id', $item->product_id)"
                                placeholder="Select Product"
                                :error="$errors->first('items.' . $index . '.product_id')" />
                        </div>
                        <div>
                            <x-ui.input
                                name="items[{{ $index }}][quantity]"
                                :id="'items_' . $index . '_quantity'"
                                label="Quantity"
                                type="number"
                                :value="old('items.' . $index . '.quantity', $item->quantity ?? 1)"
                                placeholder="Qty"
                                :required="true"
                                min="1"
                                :error="$errors->first('items.' . $index . '.quantity')" />
                        </div>
                        <div>
                            <x-ui.input
                                name="items[{{ $index }}][notes]"
                                :id="'items_' . $index . '_notes'"
                                label="Notes"
                                :value="old('items.' . $index . '.notes', $item->notes ?? '')"
                                placeholder="Notes (optional)"
                                :error="$errors->first('items.' . $index . '.notes')" />
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="remove-item-btn btn btn-sm btn-danger text-white px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600">
                                <i class="ri-delete-bin-line"></i> Remove
                            </button>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="order-item-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 p-4 border border-slate-200 rounded-lg">
                        <div>
                            <x-ui.select2
                                name="items[0][product_id]"
                                id="items_0_product_id"
                                label="Product"
                                :required="true"
                                :options="$products->map(function($product) {
                                        return ['value' => $product->id, 'label' => $product->name . ' (Rp ' . number_format($product->price, 0, ',', '.') . ')'];
                                    })->toArray()"
                                option-value="value"
                                option-label="label"
                                :selected="old('items.0.product_id')"
                                placeholder="Select Product"
                                :error="$errors->first('items.0.product_id')" />
                        </div>
                        <div>
                            <x-ui.input
                                name="items[0][quantity]"
                                id="items_0_quantity"
                                label="Quantity"
                                type="number"
                                value="1"
                                placeholder="Qty"
                                :required="true"
                                min="1"
                                :error="$errors->first('items.0.quantity')" />
                        </div>
                        <div>
                            <x-ui.input
                                name="items[0][notes]"
                                id="items_0_notes"
                                label="Notes"
                                value=""
                                placeholder="Notes (optional)"
                                :error="$errors->first('items.0.notes')" />
                        </div>
                        <div class="flex items-end">
                            <button type="button" class="remove-item-btn btn btn-sm btn-danger text-white px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600">
                                <i class="ri-delete-bin-line"></i> Remove Item
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <button type="button" id="add-item-btn" class="btn btn-sm btn-secondary px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-50">
                    <i class="ri-add-line"></i> Add Item
                </button>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <x-ui.button type="button" size="sm" style="secondary" onclick="window.location.href='{{ route('orders.index') }}'">
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

@push('scripts')
<script>
    let itemIndex = {
        {
            isset($order) && $order - > items ? $order - > items - > count() : 1
        }
    };

    document.getElementById('add-item-btn').addEventListener('click', function() {
        const container = document.getElementById('order-items-container');
        const newRow = document.createElement('div');
        newRow.className = 'order-item-row grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 p-4 border border-slate-200 rounded-lg';
        newRow.innerHTML = `
            <div>
                <x-ui.select2
                    name="items[${itemIndex}][product_id]"
                    id="items_${itemIndex}_product_id"
                    label="Product"
                    :required="true"
                    :options="$products->map(function($product) {
                        return ['value' => $product->id, 'label' => $product->name . ' (Rp ' . number_format($product->price, 0, ',', '.') . ')'];
                    })->toArray()"
                    option-value="value"
                    option-label="label"
                    placeholder="Select Product"
                />
            </div>
            <div>
                <x-ui.input
                    name="items[${itemIndex}][quantity]"
                    id="items_${itemIndex}_quantity"
                    label="Quantity"
                    type="number"
                    value="1"
                    placeholder="Qty"
                    :required="true"
                    min="1"
                />
            </div>
            <div>
                <x-ui.input
                    name="items[${itemIndex}][notes]"
                    id="items_${itemIndex}_notes"
                    label="Notes"
                    value=""
                    placeholder="Notes (optional)"
                />
            </div>
            <div class="flex items-end">
                <button type="button" class="remove-item-btn btn btn-sm btn-danger text-white px-3 py-2 rounded-lg bg-red-500 hover:bg-red-600">
                    <i class="ri-delete-bin-line"></i> Remove
                </button>
            </div>
        `;
        container.appendChild(newRow);
        itemIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item-btn')) {
            const row = e.target.closest('.order-item-row');
            if (document.querySelectorAll('.order-item-row').length > 1) {
                row.remove();
            } else {
                alert('Minimal 1 item required!');
            }
        }
    });
</script>
@endpush