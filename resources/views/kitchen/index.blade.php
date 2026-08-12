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
    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <p class="text-xs text-slate-500">Total Orders</p>
            <p class="text-2xl font-satoshi-bold text-slate-900">{{ $orders->count() }}</p>
        </div>
        <div class="bg-yellow-50 rounded-xl shadow-sm border border-yellow-200 p-4">
            <p class="text-xs text-yellow-600">Pending</p>
            <p class="text-2xl font-satoshi-bold text-yellow-700">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-200 p-4">
            <p class="text-xs text-blue-600">Processing</p>
            <p class="text-2xl font-satoshi-bold text-blue-700">{{ $processingCount ?? 0 }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between mb-6">
        <h5 class="text-lg font-satoshi-bold text-slate-900">Kitchen Orders</h5>
        <div class="flex items-center gap-2">
            <button onclick="checkNewOrders()" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition text-sm">
                <i class="ri-refresh-line"></i> Check New
            </button>
            <a href="{{ route('kitchen.history') }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm">
                <i class="ri-history-line"></i> History
            </a>
        </div>
    </div>

    {{-- Order Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="order-container">
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

                {{-- Item List with Notes & Status --}}
                <div class="mt-3 pt-3 border-t border-slate-100 space-y-1">
                    @foreach($order->items as $item)
                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <span class="font-satoshi-medium">{{ $item->product_name }}</span>
                                <span class="text-slate-400">x{{ $item->quantity }}</span>
                                @if($item->notes)
                                    <span class="text-yellow-600 text-[10px] block italic">📝 {{ $item->notes }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-satoshi-medium
                                    {{ $item->kitchen_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $item->kitchen_status === 'cooking' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $item->kitchen_status === 'ready' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $item->kitchen_status === 'served' ? 'bg-slate-100 text-slate-500' : '' }}">
                                    {{ ucfirst($item->kitchen_status) }}
                                </span>
                                @if($item->kitchen_status === 'pending')
                                    <button @click="updateItemStatus({{ $item->id }}, 'cooking')" 
                                            class="px-1.5 py-0.5 bg-blue-500 text-white text-[10px] rounded hover:bg-blue-600 transition">
                                        Cook
                                    </button>
                                @endif
                                @if($item->kitchen_status === 'cooking')
                                    <button @click="updateItemStatus({{ $item->id }}, 'ready')" 
                                            class="px-1.5 py-0.5 bg-green-500 text-white text-[10px] rounded hover:bg-green-600 transition">
                                        Ready
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 pt-3 border-t border-slate-100">
                    <div class="flex flex-wrap items-center gap-1.5">
                        @if($order->status_order === 'pending')
                            <button @click="updateStatus('processing')" 
                                    class="px-3 py-1.5 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition">
                                Process All
                            </button>
                        @endif

                        @if($order->status_order === 'processing')
                            <button @click="updateStatus('completed')" 
                                    class="px-3 py-1.5 bg-green-500 text-white text-xs rounded-lg hover:bg-green-600 transition">
                                Complete All
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
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Success', text: data.message, timer: 1500, showConfirmButton: false });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update order status' });
                });
            },

            updateItemStatus(itemId, status) {
                fetch('/kitchen/item/' + itemId + '/status', {
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
                        Swal.fire({ icon: 'success', title: 'Success', text: data.message, timer: 1500, showConfirmButton: false });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update item status' });
                });
            }
        };
    }

    // ===================== KITCHEN NOTIFICATION =====================
    let lastCheck = new Date().toISOString();

    function checkNewOrders() {
        fetch('/kitchen/new-orders?last_check=' + encodeURIComponent(lastCheck))
            .then(res => res.json())
            .then(data => {
                if (data.count > 0) {
                    Swal.fire({
                        icon: 'info',
                        title: data.count + ' New Order(s)!',
                        text: 'Click OK to refresh',
                        timer: 5000,
                        showConfirmButton: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'No New Orders',
                        text: 'No new orders since last check',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
                lastCheck = new Date().toISOString();
            })
            .catch(error => {
                console.error('Error checking new orders:', error);
            });
    }

    // Auto check every 30 seconds
    setInterval(checkNewOrders, 30000);
</script>
@endpush