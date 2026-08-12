@php
    $title = 'Kitchen History';
    $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
@endphp

@extends('layouts.backend.main')

@section('title', $title)
@section('sub_title', $title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
<div class="space-y-8 pb-12">
    <div class="flex items-center justify-between mb-6">
        <h5 class="text-lg font-satoshi-bold text-slate-900">Completed Orders</h5>
        <a href="{{ route('kitchen.index') }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm">
            <i class="ri-arrow-left-line"></i> Back to Kitchen
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-satoshi-medium text-slate-500 uppercase">Invoice</th>
                    <th class="px-4 py-3 text-left text-xs font-satoshi-medium text-slate-500 uppercase">Table</th>
                    <th class="px-4 py-3 text-left text-xs font-satoshi-medium text-slate-500 uppercase">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-satoshi-medium text-slate-500 uppercase">Items</th>
                    <th class="px-4 py-3 text-left text-xs font-satoshi-medium text-slate-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-satoshi-medium text-slate-500 uppercase">Completed</th>
                    <th class="px-4 py-3 text-center text-xs font-satoshi-medium text-slate-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-sm font-satoshi-medium">{{ $order->code_invoice }}</td>
                        <td class="px-4 py-3 text-sm">{{ $order->table->number_table ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $order->customer_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $order->items->count() }}</td>
                        <td class="px-4 py-3 text-sm font-satoshi-medium">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $order->updated_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('kitchen.show', $order->uuid) }}" class="text-slate-400 hover:text-slate-600 transition">
                                <i class="ri-eye-line"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">No completed orders yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection