@php
    $title = 'Order Detail - ' . $order->code_invoice;
@endphp

@extends('layouts.backend.main')

@section('title', $title)

@section('content')
<div class="space-y-8 pb-12">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h5 class="text-lg font-satoshi-bold text-slate-900">Order #{{ $order->code_invoice }}</h5>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-sm rounded-full font-satoshi-medium
                    {{ $order->status_order === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $order->status_order === 'processing' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $order->status_order === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $order->status_order === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ ucfirst($order->status_order) }}
                </span>
                <a href="{{ route('kitchen.index') }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition">
                    <i class="ri-arrow-left-line"></i> Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-slate-50 rounded-lg">
            <div>
                <p class="text-xs text-slate-500">Invoice</p>
                <p class="font-satoshi-medium">{{ $order->code_invoice }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Table</p>
                <p class="font-satoshi-medium">{{ $order->table->number_table ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Customer</p>
                <p class="font-satoshi-medium">{{ $order->customer_name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Cashier</p>
                <p class="font-satoshi-medium">{{ $order->cashier->name ?? '-' }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="border border-slate-200 px-4 py-2 text-left">Product</th>
                        <th class="border border-slate-200 px-4 py-2 text-right">Qty</th>
                        <th class="border border-slate-200 px-4 py-2 text-right">Price</th>
                        <th class="border border-slate-200 px-4 py-2 text-right">Subtotal</th>
                        <th class="border border-slate-200 px-4 py-2 text-left">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td class="border border-slate-200 px-4 py-2">{{ $item->product_name }}</td>
                            <td class="border border-slate-200 px-4 py-2 text-right">{{ $item->quantity }}</td>
                            <td class="border border-slate-200 px-4 py-2 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="border border-slate-200 px-4 py-2 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td class="border border-slate-200 px-4 py-2">{{ $item->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold">
                    <tr>
                        <td colspan="3" class="border border-slate-200 px-4 py-2 text-right">Subtotal</td>
                        <td class="border border-slate-200 px-4 py-2 text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border border-slate-200 px-4 py-2 text-right">Tax</td>
                        <td class="border border-slate-200 px-4 py-2 text-right">Rp {{ number_format($order->tax, 0, ',', '.') }}</td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border border-slate-200 px-4 py-2 text-right text-lg">Grand Total</td>
                        <td class="border border-slate-200 px-4 py-2 text-right text-lg text-green-600">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-6 flex items-center gap-2">
            @if($order->status_order === 'pending')
                <form action="{{ route('kitchen.status', $order->uuid) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="processing">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                        Process Order
                    </button>
                </form>
            @endif

            @if($order->status_order === 'processing')
                <form action="{{ route('kitchen.status', $order->uuid) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                        Complete Order
                    </button>
                </form>
            @endif

            @if($order->status_order !== 'completed' && $order->status_order !== 'cancelled')
                <form action="{{ route('kitchen.status', $order->uuid) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                        Cancel Order
                    </button>
                </form>
            @endif

            <a href="{{ route('kitchen.print', $order->uuid) }}" target="_blank" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">
                <i class="ri-printer-line"></i> Print
            </a>
        </div>
    </div>
</div>
@endsection