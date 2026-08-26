@php
    $title = 'Admin Panel';
    $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
@endphp

@extends('layouts.backend.main')

@section('title', $title)
@section('sub_title', $sub_title ?? $title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
<div class="space-y-8 pb-12">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-satoshi-bold tracking-tight text-slate-900">Admin Panel</h1>
        <p class="text-base text-slate-500 mt-1">Centralized control panel for managing your restaurant operations.</p>
    </div>

    <!-- Quick Access Shortcuts -->
    @php
        $shortcuts = [
            [
                'label' => 'Point of Sale',
                'icon' => 'ri-shopping-cart-2-line',
                'route' => route('pos.index'),
            ],
            [
                'label' => 'Kitchen Display',
                'icon' => 'ri-restaurant-2-line',
                'route' => route('kitchen.index'),
            ],
            [
                'label' => 'Orders',
                'icon' => 'ri-file-list-3-line',
                'route' => route('orders.index'),
            ],
            [
                'label' => 'Outlets',
                'icon' => 'ri-store-2-line',
                'route' => route('outlet.index'),
            ],
            [
                'label' => 'Products',
                'icon' => 'ri-shopping-bag-3-line',
                'route' => route('products.index'),
            ],
            [
                'label' => 'Users & Roles',
                'icon' => 'ri-user-settings-line',
                'route' => route('user.index'),
            ],
        ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($shortcuts as $shortcut)
            <a href="{{ $shortcut['route'] }}"
               class="x-ui-card block rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800">
                        <i class="{{ $shortcut['icon'] }} text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-satoshi-bold text-slate-900">{{ $shortcut['label'] }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Open module</p>
                    </div>
                    <i class="ri-arrow-right-s-line text-xl text-slate-300"></i>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Placeholder Sections (isi menyusul) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @php
            $sections = [
                ['title' => 'Sales Analytics', 'subtitle' => 'Revenue trends, top products, and hourly performance', 'icon' => 'ri-bar-chart-grouped-line'],
                ['title' => 'Staff Performance', 'subtitle' => 'Cashier activity, shifts, and productivity metrics', 'icon' => 'ri-team-line'],
                ['title' => 'Inventory Health', 'subtitle' => 'Low stock alerts, wastage, and stock turnover', 'icon' => 'ri-archive-line'],
                ['title' => 'Reports Center', 'subtitle' => 'Exportable daily, weekly, and monthly reports', 'icon' => 'ri-file-chart-line'],
            ];
        @endphp
        @foreach($sections as $section)
            <x-ui.card class="p-6 flex flex-col hover:shadow-xl transition-all duration-300">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-500 border border-slate-200">
                        <i class="{{ $section['icon'] }} text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-satoshi-bold text-slate-900 text-base">{{ $section['title'] }}</h3>
                        <p class="text-xs text-slate-500">{{ $section['subtitle'] }}</p>
                    </div>
                </div>

                {{-- Empty state placeholder --}}
                <div class="flex-1 flex flex-col items-center justify-center py-10 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-50 border border-dashed border-slate-200 mb-3">
                        <i class="ri-inbox-archive-line text-2xl text-slate-300"></i>
                    </div>
                    <p class="text-sm font-satoshi-medium text-slate-400">Coming soon</p>
                    <p class="text-xs text-slate-400 mt-1">This section will be populated in the next iteration.</p>
                </div>
            </x-ui.card>
        @endforeach
    </div>
</div>
@endsection
