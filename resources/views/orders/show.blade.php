@php
    $title = 'Order Detail';
@endphp

@extends('layouts.backend.main')

@section('title', $title)

@section('content')
<div class="space-y-8 pb-12">
    <x-ui.card>
        <div class="flex items-center justify-between mb-6">
            <h5 class="text-lg font-satoshi-bold text-slate-900 mb-0">Order #{{ $order->code_invoice }}</h5>
            <a href="{{ route('orders.index') }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50">
                <i class="ri-arrow-left-line mr-1"></i> Back
            </a>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p><strong>Customer:</strong> {{ $order->customer_name ?? '-' }}</p>
                <p><strong>Outlet:</strong> {{ $order->outlet->name ?? '-' }}</p>
                <p><strong>Cashier:</strong> {{ $order->cashier->name ?? '-' }}</p>
                <p><strong>Table:</strong> {{ $order->table->table_number ?? '-' }}</p>
            </div>
            <div>
                <p><strong>Status:</strong> <span class="badge bg-{{ ['pending' => 'warning', 'processing' => 'info', 'completed' => 'success', 'cancelled' => 'danger'][$order->status_order] ?? 'secondary' }}">{{ ucfirst($order->status_order) }}</span></p>
                <p><strong>Payment:</strong> {{ ucfirst($order->payment_method) }}</p>
                <p><strong>Date:</strong> {{ $order->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="border border-slate-200 px-4 py-2 text-center">Product</th>
                        <th class="border border-slate-200 px-4 py-2 text-center">Qty</th>
                        <th class="border border-slate-200 px-4 py-2 text-center">Price</th>
                        <th class="border border-slate-200 px-4 py-2 text-center">Subtotal</th>
                        <th class="border border-slate-200 px-4 py-2 text-center">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td class="border border-slate-200 px-4 py-2">{{ $item->product_name }}</td>
                            <td class="border border-slate-200 px-4 py-2 text-left">{{ $item->quantity }}</td>
                            <td class="border border-slate-200 px-4 py-2 text-left">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="border border-slate-200 px-4 py-2 text-left">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td class="border border-slate-200 px-4 py-2">{{ $item->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold">
                    <tr>
                        <td colspan="3" class="border border-slate-200 px-4 py-2 text-left">Subtotal :</td>
                        <td class="border border-slate-200 px-4 py-2 text-left">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border border-slate-200 px-4 py-2 text-left">Tax (10%) :</td>
                        <td class="border border-slate-200 px-4 py-2 text-left">Rp {{ number_format($order->tax, 0, ',', '.') }}</td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border border-slate-200 px-4 py-2 text-left text-lg">Grand Total :</td>
                        <td class="border border-slate-200 px-4 py-2 text-left text-lg text-green-600">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-ui.card>
</div>
@endsection