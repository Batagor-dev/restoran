@php
    $title = 'Order Detail - ' . $order->code_invoice;
@endphp

@extends('layouts.backend.main')

@section('title', $title)

@section('content')
<div class="space-y-8 pb-12" x-data="kitchenDetail({{ $order->id }})">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h5 class="text-lg font-satoshi-bold text-slate-900">Order #{{ $order->code_invoice }}</h5>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-sm rounded-full font-satoshi-medium
                    {{ $order->status_order === 'pending' ? 'bg-slate-100 text-slate-600' : '' }}
                    {{ $order->status_order === 'processing' ? 'bg-slate-900 text-white' : '' }}
                    {{ $order->status_order === 'completed' ? 'bg-emerald-50 text-emerald-600' : '' }}
                    {{ $order->status_order === 'cancelled' ? 'bg-rose-50 text-rose-600' : '' }}">
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
                <p class="text-xs text-slate-500">Outlet</p>
                <p class="font-satoshi-medium">{{ $order->outlet?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Order Type</p>
                <p class="font-satoshi-medium">{{ $order->order_type === 'takeaway' ? 'Take Away' : 'Dine In' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Table</p>
                <p class="font-satoshi-medium">{{ $order->table?->number_table ?? '-' }}</p>
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
                        <td class="border border-slate-200 px-4 py-2 text-right text-lg text-emerald-600">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-6 flex items-center gap-2">
            @if($order->status_order === 'pending')
                <button type="button" @click="updateStatus('processing')"
                        class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition cursor-pointer">
                    Process Order
                </button>
            @endif

            @if($order->status_order === 'processing')
                <button type="button" @click="updateStatus('completed')"
                        class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition cursor-pointer">
                    Complete Order
                </button>
            @endif

            @if($order->status_order !== 'completed' && $order->status_order !== 'cancelled')
                <button type="button" @click="updateStatus('cancelled')"
                        class="px-4 py-2 bg-rose-500 text-white rounded-lg hover:bg-rose-600 transition cursor-pointer">
                    Cancel Order
                </button>
            @endif

            <a href="{{ route('kitchen.print', $order->uuid) }}" target="_blank" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">
                <i class="ri-printer-line"></i> Print
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('kitchenDetail', (orderId) => ({
            updateStatus(status) {
                if (!confirm('Update order to "' + status + '"?')) return;

                fetch('/kitchen/' + orderId + '/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        createToast('success', data.message);
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        createToast('error', data.message || 'Failed to update order status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    createToast('error', 'Failed to update order status');
                });
            }
        }));
    });
</script>
@endpush