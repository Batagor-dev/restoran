@php
    $title = 'Revenue Report';
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
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h5 class="text-lg font-satoshi-bold text-slate-900 mb-0">Laporan Pendapatan</h5>
            <p class="text-sm text-slate-500 mt-1">
                Periode: {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }} · Outlet aktif
            </p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <button type="button" onclick="window.print()"
                    class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium no-print">
                <i class="ri-printer-line mr-1"></i> Print
            </button>
            <a href="{{ route('transactions.index') }}"
               class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium no-print">
                <i class="ri-arrow-left-line mr-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <x-ui.card class="p-6 no-print">
        <form method="GET" action="{{ route('transactions.report') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
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
            <div>
                <label for="cashier_id" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">Cashier</label>
                <select id="cashier_id" name="cashier_id" class="report-filter w-full">
                    <option value="">All Cashiers</option>
                    @foreach($cashiers as $user)
                        <option value="{{ $user->id }}" {{ request('cashier_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="payment_method" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">Payment</label>
                <select id="payment_method" name="payment_method" class="report-filter w-full">
                    <option value="">All Methods</option>
                    @foreach(['cash','qris','debit','credit'] as $method)
                        <option value="{{ $method }}" {{ request('payment_method') === $method ? 'selected' : '' }}>{{ strtoupper($method) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition text-sm font-satoshi-medium">
                <i class="ri-filter-3-line mr-1"></i> Apply Filter
            </button>
        </form>
    </x-ui.card>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        @php
            $summaryCards = [
                ['label' => 'Gross Sales', 'value' => $summary['gross_sales'], 'icon' => 'ri-shopping-bag-3-line', 'note' => $summary['transaction_count'].' transaction(s)'],
                ['label' => 'Voucher / Discount', 'value' => $summary['discount'], 'icon' => 'ri-coupon-line', 'note' => 'total discount given'],
                ['label' => 'Net Revenue', 'value' => $summary['net_revenue'], 'icon' => 'ri-money-dollar-circle-line', 'note' => $summary['tax'] > 0 ? 'incl. tax Rp '.number_format($summary['tax'], 0, ',', '.') : '-'],
            ];
        @endphp
        @foreach($summaryCards as $card)
            <x-ui.card class="p-6 flex flex-col justify-between min-h-[140px] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800">
                        <i class="{{ $card['icon'] }} text-lg"></i>
                    </div>
                    <span class="text-sm font-satoshi-bold text-slate-900">{{ $card['label'] }}</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-xl font-satoshi-bold tracking-tight text-slate-900">Rp {{ number_format($card['value'], 0, ',', '.') }}</h3>
                    <p class="text-xs font-satoshi-medium text-slate-500 mt-1">{{ $card['note'] }}</p>
                </div>
            </x-ui.card>
        @endforeach
    </div>

    <!-- Penyesuaian (Refund & Void) -->
    <x-ui.card class="p-6">
        <h6 class="font-satoshi-bold text-slate-900 mb-4">Penyesuaian</h6>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                <span class="text-slate-600 font-satoshi-medium">Refunded ({{ $summary['refunded_count'] }})</span>
                <strong class="text-rose-600">-Rp {{ number_format($summary['refunded_total'], 0, ',', '.') }}</strong>
            </div>
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                <span class="text-slate-600 font-satoshi-medium">Voided ({{ $summary['voided_count'] }})</span>
                <strong class="text-rose-600">-Rp {{ number_format($summary['voided_total'], 0, ',', '.') }}</strong>
            </div>
            <div class="p-4 bg-slate-950 border border-slate-800 rounded-xl flex items-center justify-between">
                <span class="text-white/80 font-satoshi-medium">Final Revenue</span>
                <strong class="text-white text-base">Rp {{ number_format($summary['after_adjustment'], 0, ',', '.') }}</strong>
            </div>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Breakdown Harian -->
        <x-ui.card class="lg:col-span-2 p-6">
            <h6 class="font-satoshi-bold text-slate-900 mb-4">Breakdown Harian</h6>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500 font-satoshi-semibold">
                            <th class="py-3 pr-4 text-left">Date</th>
                            <th class="py-3 px-4 text-right">Trx</th>
                            <th class="py-3 px-4 text-right">Gross Sales</th>
                            <th class="py-3 px-4 text-right">Discount</th>
                            <th class="py-3 pl-4 text-right">Net Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 font-satoshi-medium text-slate-700">
                        @forelse($daily as $row)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 pr-4">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                                <td class="py-3 px-4 text-right">{{ $row->transactions }}</td>
                                <td class="py-3 px-4 text-right">Rp {{ number_format($row->gross_sales, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-red-500">-Rp {{ number_format($row->discount, 0, ',', '.') }}</td>
                                <td class="py-3 pl-4 text-right font-satoshi-bold">Rp {{ number_format($row->net_revenue, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-slate-400">No transactions in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <!-- Per Payment Method -->
        <x-ui.card class="p-6">
            <h6 class="font-satoshi-bold text-slate-900 mb-4">Per Metode Pembayaran</h6>
            <div class="divide-y divide-slate-100/70">
                @forelse($byPayment as $row)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div>
                            <p class="text-sm font-satoshi-bold text-slate-900 uppercase">{{ $row->payment_method }}</p>
                            <p class="text-xs text-slate-500">{{ $row->transactions }} transaction(s)</p>
                        </div>
                        <p class="text-sm font-satoshi-bold text-slate-900">Rp {{ number_format($row->total, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-400">No data.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
@endsection

@push('scripts')
    <script>
    $(document).ready(function () {
        $('.report-filter').select2({ width: '100%', placeholder: 'All', allowClear: true });
    });
    </script>
@endpush
