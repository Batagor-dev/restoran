@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Customer Form';

    if (isset($customer_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $customer_data);
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    }
    $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();
@endphp

@extends('layouts.backend.main')

@section('title', 'Customer Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ $action }}" class="space-y-6">
                @isset($customer_data) @method('PUT') @endisset
                @csrf

                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900 mb-6">{{ $sub_title }}</h5>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Customer Name -->
                        <div class="md:col-span-2">
                            <x-ui.input
                                name="name"
                                label="Customer Name"
                                placeholder="Enter customer name"
                                value="{{ old('name', $customer_data->name ?? '') }}"
                                required
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <x-ui.input
                                type="email"
                                name="email"
                                label="Email"
                                placeholder="Enter email (optional)"
                                value="{{ old('email', $customer_data->email ?? '') }}"
                            />
                        </div>

                        <!-- Phone -->
                        <div>
                            <x-ui.input
                                name="phone"
                                label="Phone"
                                placeholder="Enter phone number (optional)"
                                value="{{ old('phone', $customer_data->phone ?? '') }}"
                            />
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-satoshi-medium text-slate-700 mb-2">Address</label>
                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-base font-satoshi-medium text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200 transition-all"
                                placeholder="Enter address (optional)"
                            >{{ old('address', $customer_data->address ?? '') }}</textarea>
                            @error('address')
                                <p class="mt-1.5 block text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="md:col-span-2">
                            <x-ui.switch
                                name="is_active"
                                label="Active Status"
                                :checked="old('is_active', $customer_data->is_active ?? true)"
                                value="1"
                            />
                        </div>
                    </div>
                </div>

                <!-- Submit / Cancel -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ $breadcrumb_parent?->url ?? route('customers.index') }}'">
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
