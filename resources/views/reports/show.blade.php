@php
    $title = $meta['label'];
    $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $type);

    // Laporan snapshot tidak memakai rentang tanggal
    $needsDates = $meta['dates'];

    $exportUrl = route('reports.export', $type)
        .'?'.http_build_query(array_filter([
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
            'threshold' => $type === 'low-stock' ? request('threshold', 5) : null,
        ]));
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
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <h5 class="text-lg font-satoshi-bold text-slate-900 mb-1">{{ $meta['label'] }}</h5>
            <p class="text-sm text-slate-500">
                {{ $meta['desc'] }}
                @if($needsDates)
                    &middot; Periode: {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}
                @else
                    &middot; Snapshot outlet aktif
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start lg:self-auto no-print">
            <a href="{{ route('reports.index') }}"
               class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium">
                <i class="ri-arrow-left-line mr-1"></i> Back
            </a>
            <button type="button" onclick="window.print()"
                    class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium">
                <i class="ri-printer-line mr-1"></i> Print
            </button>
            <a href="{{ $exportUrl }}" target="_blank"
               class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition text-sm font-satoshi-medium">
                <i class="ri-download-2-line mr-1"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Filter -->
    <x-ui.card class="p-6 no-print">
        <form method="GET" action="{{ route('reports.show', $type) }}"
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ $needsDates ? 5 : 3 }} gap-4 items-end">
            @if($needsDates)
                <div>
                    <label for="start_date" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-satoshi-medium text-slate-900 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200 transition">
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-satoshi-medium text-slate-900 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200 transition">
                </div>
            @endif

            @if($type === 'low-stock')
                <div>
                    <label for="threshold" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">Low Stock Threshold</label>
                    <input type="number" id="threshold" name="threshold" min="0" value="{{ $threshold }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-satoshi-medium text-slate-900 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200 transition">
                </div>
            @endif

            @unless($type === 'stock')
                <button type="submit"
                        class="px-4 py-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition text-sm font-satoshi-medium h-[46px]">
                    <i class="ri-filter-3-line mr-1"></i> Apply Filter
                </button>
            @endunless
        </form>

        @if($type === 'profit')
            <p class="text-xs text-slate-500 mt-4">
                <i class="ri-information-line"></i> Produk bertanda * belum memiliki HPP (Cost Price) — biaya dihitung Rp 0 sehingga profit tampak lebih tinggi. Isi Cost Price di form produk agar akurat.
            </p>
        @endif
    </x-ui.card>

    <!-- Summary Cards -->
    @if(!empty($summary))
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ count($summary) > 3 ? 3 : count($summary) }} gap-5">
            @foreach($summary as $card)
                <x-ui.card class="p-6 flex flex-col justify-between min-h-[120px] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <span class="text-sm font-satoshi-bold text-slate-900">{{ $card['label'] }}</span>
                    <h3 class="mt-3 text-xl font-satoshi-bold tracking-tight {{ ($card['strong'] ?? false) ? 'text-slate-900' : 'text-slate-700' }}">
                        {{ $card['value'] }}
                    </h3>
                </x-ui.card>
            @endforeach
        </div>
    @endif

    <!-- Data Table -->
    <x-ui.card class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500 font-satoshi-semibold">
                        <th class="py-3 pr-4 text-left w-12">No</th>
                        @foreach($columns as $column)
                            <th class="py-3 px-4 {{ $column['align'] === 'right' ? 'text-right' : 'text-left' }}">{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 font-satoshi-medium text-slate-700">
                    @forelse($rows as $row)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 pr-4 text-slate-400">{{ $loop->iteration }}</td>
                            @foreach($columns as $column)
                                <td class="py-3 px-4 {{ $column['align'] === 'right' ? 'text-right' : 'text-left' }} {{ $loop->last && $column['align'] === 'right' ? 'font-satoshi-bold text-slate-900' : '' }}">
                                    {{ $row[$column['key']] ?? '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($columns) + 1 }}" class="py-10 text-center text-slate-400">No data available for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
@endsection

@push('styles')
    @media print {
        #admin-sidebar, #admin-header, .no-print { display: none !important; }
        main, .lg\:pl-\[280px\] { padding-left: 0 !important; }
    }
@endpush
