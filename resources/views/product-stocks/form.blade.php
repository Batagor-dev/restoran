@php
    $product_stock_data = $product_stock_data ?? $productStock ?? null;
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Product Stock Form';

    if (isset($product_stock_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $product_stock_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();

    $productOptions = isset($products) ? (is_array($products) ? $products : $products->toArray()) : [];
    $outletOptions = isset($outlets) ? (is_array($outlets) ? $outlets : $outlets->toArray()) : [];
@endphp

@extends('layouts.backend.main')

@section('title', 'Product Stock Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ $action }}" class="space-y-6">
                @isset($product_stock_data) @method('PUT') @endisset
                @csrf

                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-6">{{ $sub_title }}</h5>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(!isset($product_stock_data))
                            <!-- Product -->
                            <div>
                                <x-ui.select2 
                                    name="product_id" 
                                    label="Product"
                                    placeholder="Select Product"
                                    :value="old('product_id', '')"
                                    :options="$productOptions"
                                    required
                                />
                            </div>

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

                            <!-- Outlet -->
                            <div>
                                <x-ui.select2 
                                    name="outlet_id" 
                                    label="Outlet"
                                    placeholder="Select Outlet"
                                    :value="old('outlet_id', '')"
                                    :options="$outletOptions"
                                    required
                                />
                            </div>
                        @else
                            <div class="md:col-span-2 bg-slate-50 rounded-2xl p-4 text-sm font-satoshi-medium text-slate-700 border border-slate-200/80 space-y-1">
                                <p><span class="text-slate-500 font-semibold">Product:</span> <strong class="text-slate-900">{{ $product_stock_data->product->name ?? '-' }}</strong></p>
                                <p><span class="text-slate-500 font-semibold">Outlet:</span> <strong class="text-slate-900">{{ $product_stock_data->outlet->name ?? '-' }}</strong></p>
                            </div>
                        @endif

                        <!-- Quantity -->
                        <div class="md:col-span-2">
                            <x-ui.input 
                                type="number"
                                name="quantity" 
                                label="Quantity" 
                                placeholder="Enter quantity" 
                                value="{{ old('quantity', $product_stock_data->quantity ?? 0) }}"
                                min="0"
                                required
                            />
                        </div>
                    </div>
                </div>

                <!-- Submit / Cancel -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ $breadcrumb_parent?->url ?? route('product-stocks.index') }}'">
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
