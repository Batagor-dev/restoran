@php
    $isEdit = isset($customer);
    $title = $isEdit ? 'Edit Customer' : 'Add Customer';
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
                        label="Name"
                        :value="old('name', $customer->name ?? '')"
                        placeholder="Enter customer name"
                        :required="true"
                        :error="$errors->first('name')"
                    />
                </div>

                {{-- Email --}}
                <div>
                    <x-ui.input
                        name="email"
                        id="email"
                        label="Email"
                        type="email"
                        :value="old('email', $customer->email ?? '')"
                        placeholder="Enter email (optional)"
                        :error="$errors->first('email')"
                    />
                </div>

                {{-- Phone --}}
                <div>
                    <x-ui.input
                        name="phone"
                        id="phone"
                        label="Phone"
                        :value="old('phone', $customer->phone ?? '')"
                        placeholder="Enter phone number (optional)"
                        :error="$errors->first('phone')"
                    />
                </div>

                {{-- Address --}}
                <div>
                    <x-ui.textarea
                        name="address"
                        id="address"
                        label="Address"
                        :value="old('address', $customer->address ?? '')"
                        placeholder="Enter address (optional)"
                        rows="3"
                        :error="$errors->first('address')"
                    />
                </div>

                {{-- Is Active --}}
                <div>
                    <x-ui.checkbox
                        name="is_active"
                        id="is_active"
                        label="Active"
                        :checked="old('is_active', $customer->is_active ?? true)"
                        value="1"
                    />
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <x-ui.button type="submit" size="sm">
                    <i class="ri-save-line mr-1"></i> Save
                </x-ui.button>
                <x-ui.button type="button" size="sm" style="secondary" onclick="window.location.href='{{ route('customers.index') }}'">
                    Cancel
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection