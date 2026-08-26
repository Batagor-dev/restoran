@php
    $title = 'Reports';
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
        <h1 class="text-2xl font-satoshi-bold tracking-tight text-slate-900">Reports</h1>
        <p class="text-base text-slate-500 mt-1">
            Analisis operasional outlet aktif: penjualan, stok, kasir, hingga profitabilitas.
        </p>
    </div>

    <!-- Report Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($registry as $type => $meta)
            <a href="{{ route('reports.show', $type) }}"
               class="block rounded-2xl border border-slate-100 bg-white p-6 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800 flex-shrink-0">
                        <i class="{{ $meta['icon'] }} text-xl"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-satoshi-bold text-slate-900 truncate">{{ $meta['label'] }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $meta['desc'] }}</p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-50 flex items-center justify-between text-xs text-slate-400">
                    <span>{{ $meta['dates'] ? 'Periode tertentu' : 'Snapshot terkini' }}</span>
                    <i class="ri-arrow-right-s-line text-base group-hover:text-slate-900 transition-colors"></i>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Related -->
    <x-ui.card class="p-6">
        <h6 class="font-satoshi-bold text-slate-900 mb-3">Laporan Terkait</h6>
        <div class="flex flex-wrap gap-3 text-sm">
            @can('Transaction Report')
                <a href="{{ route('transactions.report') }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition font-satoshi-medium">
                    <i class="ri-money-dollar-circle-line mr-1"></i> Revenue Report (Transactions)
                </a>
            @endcan
            @can('Transaction Access')
                <a href="{{ route('transactions.index') }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition font-satoshi-medium">
                    <i class="ri-receipt-line mr-1"></i> Riwayat Transaksi
                </a>
            @endcan
            @can('Stock Movement Delete')
                <a href="{{ route('stock-movements.index') }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition font-satoshi-medium">
                    <i class="ri-arrow-left-right-line mr-1"></i> Stock Movements
                </a>
            @endcan
        </div>
    </x-ui.card>
</div>
@endsection
