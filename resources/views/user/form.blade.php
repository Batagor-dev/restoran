@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Dashboard';

    if (isset($user_data)) {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $user_data);
        $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();
        
        // Cek apakah foto menggunakan avatar default atau file upload
        $previewUrl = null;
        if ($user_data->foto) {
            $previewUrl = str_starts_with($user_data->foto, 'avatar-')
                ? asset('assets/img/avatar/' . $user_data->foto)
                : asset('storage/uploads/users/' . $user_data->foto);
        }
    } else {
        $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
        $breadcrumb_parent = $breadcrumbsData->where('title', '!=', $breadcrumb->title)->last();
        $previewUrl = null;
    }

    $outletOptions = isset($outlets) ? $outlets->map(fn($o) => ['value' => $o->id, 'label' => $o->name])->toArray() : [];
    $selectedOutlets = old('outlets', isset($user_data) ? $user_data->outlets->pluck('id')->toArray() : []);
@endphp

@extends('layouts.backend.main')

@section('title', 'User Form')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6">

        <x-ui.card>
            <form method="POST" action="{{ $action }}" class="space-y-6" enctype="multipart/form-data">
                @isset($user_data) @method('PUT') @endisset
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.input 
                        name="username" 
                        label="Username" 
                        placeholder="Username" 
                        value="{{ old('username', $user_data->username ?? '') }}"
                        required
                    />

                    <x-ui.input 
                        name="name" 
                        label="Name" 
                        placeholder="Name" 
                        value="{{ old('name', $user_data->name ?? '') }}"
                        required
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.input 
                        type="email"
                        name="email" 
                        label="Email" 
                        placeholder="Email" 
                        value="{{ old('email', $user_data->email ?? '') }}"
                        required
                    />

                    <x-ui.file
                        name="foto"
                        label="Foto"
                        :previewUrl="$previewUrl"
                    />
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <x-ui.select2
                        name="outlets"
                        label="Assign Outlets"
                        placeholder="Select Outlets..."
                        :multiple="true"
                        :options="$outletOptions"
                        :value="$selectedOutlets"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.password 
                        name="password" 
                        label="Password" 
                        placeholder="••••••••" 
                    />

                    <x-ui.password 
                        name="password_confirmation" 
                        label="Confirm Password" 
                        placeholder="••••••••" 
                    />
                </div>

                <!-- Submit / Cancel -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ $breadcrumb_parent->url }}'">
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
