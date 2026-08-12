@php
    $stock_movement_data = $stock_movement_data ?? $stockMovement ?? null;
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Stock Movement Form';

    if (isset($stock_movement_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $stock_movement_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();

    $stockOptions = isset($stocks) ? (is_array($stocks) ? $stocks : $stocks->toArray()) : [];
    $movementTypes = [
        ['value' => 'in', 'label' => 'Stock In'],
        ['value' => 'out', 'label' => 'Stock Out'],
        ['value' => 'adjustment', 'label' => 'Adjustment'],
        ['value' => 'return', 'label' => 'Return'],
    ];
@endphp

@extends('layouts.backend.main')

@section('title', 'Stock Movement Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ $action }}" class="space-y-6">
                @isset($stock_movement_data) @method('PUT') @endisset
                @csrf

                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-6">{{ $sub_title }}</h5>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Product Stock -->
                        <div>
                            <x-ui.select2 
                                name="product_stock_id" 
                                label="Product Stock"
                                placeholder="Select Product Stock"
                                :value="old('product_stock_id', $stock_movement_data->product_stock_id ?? '')"
                                :options="$stockOptions"
                                required
                            />
                        </div>

                        <!-- Movement Type -->
                        <div>
                            <x-ui.select2 
                                name="movement_type" 
                                label="Movement Type"
                                placeholder="Select Movement Type"
                                :value="old('movement_type', $stock_movement_data->movement_type ?? '')"
                                :options="$movementTypes"
                                required
                            />
                        </div>

                        <!-- Quantity -->
                        <div>
                            <x-ui.input 
                                type="number"
                                name="quantity" 
                                label="Quantity" 
                                placeholder="Enter quantity" 
                                value="{{ old('quantity', $stock_movement_data->quantity ?? 1) }}"
                                min="1"
                                required
                            />
                        </div>

                        <!-- Reference Type -->
                        <div>
                            <x-ui.input 
                                name="reference_type" 
                                label="Reference Type" 
                                placeholder="e.g. purchase, sale, return" 
                                value="{{ old('reference_type', $stock_movement_data->reference_type ?? '') }}"
                            />
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-satoshi-medium text-slate-700 mb-2">Notes</label>
                            <textarea 
                                id="notes" 
                                name="notes" 
                                rows="3" 
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base font-satoshi-medium text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all"
                                placeholder="Enter notes (optional)"
                            >{{ old('notes', $stock_movement_data->notes ?? '') }}</textarea>
                            @error('notes')
                                <p class="mt-1.5 block text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit / Cancel -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ $breadcrumb_parent?->url ?? route('stock-movements.index') }}'">
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
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <x-ui.button type="button" size="sm" style="secondary" onclick="window.location.href='{{ route('stock-movements.index') }}'">
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

    @if(session('error'))
        <script>
            Swal.fire({ icon: 'error', title: 'Error', text: "{{ session('error') }}" });
        </script>
    @endif
@endpush

