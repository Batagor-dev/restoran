@php
    $title = 'Kitchen';
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
        <h5 class="text-lg font-satoshi-bold text-slate-900">Kitchen Orders</h5>
        <button @click="refreshOrders()" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition">
            <i class="ri-refresh-line"></i> Refresh
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($orders as $order)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 hover:shadow-md transition-all duration-200" 
                 x-data="kitchenOrder({{ $order->id }})">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h6 class="font-satoshi-bold text-slate-900">#{{ $order->code_invoice }}</h6>
                        <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full font-satoshi-medium
                        {{ $order->status_order === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $order->status_order === 'processing' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $order->status_order === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $order->status_order === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ ucfirst($order->status_order) }}
                    </span>
                </div>

                <div class="space-y-1 text-sm">
                    <p><span class="text-slate-500">Table:</span> {{ $order->table->number_table ?? '-' }}</p>
                    <p><span class="text-slate-500">Customer:</span> {{ $order->customer_name ?? '-' }}</p>
                    <p><span class="text-slate-500">Items:</span> {{ $order->items->count() }}</p>
                </div>

                <div class="mt-3 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        @if($order->status_order === 'pending')
                            <button @click="updateStatus('processing')" 
                                    class="px-3 py-1.5 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition">
                                Process
                            </button>
                        @endif

                        @if($order->status_order === 'processing')
                            <button @click="updateStatus('completed')" 
                                    class="px-3 py-1.5 bg-green-500 text-white text-xs rounded-lg hover:bg-green-600 transition">
                                Complete
                            </button>
                        @endif

                        @if($order->status_order === 'pending' || $order->status_order === 'processing')
                            <button @click="updateStatus('cancelled')" 
                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition">
                                Cancel
                            </button>
                        @endif

                        <a href="{{ route('kitchen.show', $order->uuid) }}" 
                           class="px-3 py-1.5 bg-slate-200 text-slate-700 text-xs rounded-lg hover:bg-slate-300 transition">
                            Detail
                        </a>
                        <a href="{{ route('kitchen.print', $order->uuid) }}" target="_blank"
                           class="px-3 py-1.5 bg-slate-200 text-slate-700 text-xs rounded-lg hover:bg-slate-300 transition">
                            <i class="ri-printer-line"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-slate-400">
                <i class="ri-restaurant-line text-4xl block mb-2"></i>
                <p>No orders in kitchen</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function kitchenOrder(orderId) {
        return {
            updateStatus(status) {
                if (!confirm('Update order to ' + status + '?')) return;

                fetch('/kitchen/' + orderId + '/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Server error: ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error updating status'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update order status: ' + error.message
                    });
                });
            },

            refreshOrders() {
                location.reload();
            }
        };
    }
</script>
@endpush