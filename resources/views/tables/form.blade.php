@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Form Meja';

    if (isset($table_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $table_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();

    $outletOptions = [];
    if (isset($outlets)) {
        foreach ($outlets as $outlet) {
            $outletOptions[] = [
                'value' => (string) $outlet->id,
                'label' => $outlet->name,
            ];
        }
    }
@endphp

@extends('layouts.backend.main')

@section('title', 'Form Meja')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ $action }}" class="space-y-6">
                @isset($table_data) @method('PUT') @endisset
                @csrf

                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-6">{{ $sub_title }}</h5>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Outlet Selection -->
                        @if(count($outletOptions) > 0)
                            <div>
                                <x-ui.select2 
                                    name="outlet_id" 
                                    label="Outlet" 
                                    placeholder="Select outlet..."
                                    :value="old('outlet_id', $table_data->outlet_id ?? auth()->user()->current_outlet_id ?? ($outlets->first()?->id ?? ''))"
                                    :options="$outletOptions"
                                />
                            </div>
                        @endif

                        <!-- Table Number / Name -->
                        <x-ui.input 
                            name="number_table" 
                            label="Table Number / Name" 
                            placeholder="e.g. Meja 01, T-12, Outdoor 03" 
                            value="{{ old('number_table', $table_data->number_table ?? '') }}"
                            required
                        />

                        <!-- Is Active -->
                        <div class="pt-2">
                            <x-ui.checkbox 
                                name="is_active" 
                                label="Active Status" 
                                :checked="old('is_active', $table_data->is_active ?? true)"
                                value="1"
                                :reverse="true"
                            />
                        </div>
                    </div>
                </div>

                <!-- Submit / Cancel -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ $breadcrumb_parent?->url ?? route('tables.index') }}'">
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
