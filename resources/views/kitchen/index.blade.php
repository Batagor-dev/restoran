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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <x-ui.card class="p-6 flex flex-col justify-between min-h-[130px] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800">
                    <i class="ri-restaurant-2-line text-lg"></i>
                </div>
                <span class="text-sm font-satoshi-bold text-slate-900">Total Orders</span>
            </div>
            <h3 class="text-2xl font-satoshi-bold tracking-tight text-slate-900 mt-4">{{ $orders->count() }}</h3>
        </x-ui.card>

        <x-ui.card class="p-6 flex flex-col justify-between min-h-[130px] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800">
                    <i class="ri-time-line text-lg"></i>
                </div>
                <span class="text-sm font-satoshi-bold text-slate-900">Pending</span>
            </div>
            <h3 class="text-2xl font-satoshi-bold tracking-tight text-slate-900 mt-4">{{ $pendingCount ?? 0 }}</h3>
        </x-ui.card>

        <x-ui.card class="p-6 flex flex-col justify-between min-h-[130px] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800">
                    <i class="ri-fire-line text-lg"></i>
                </div>
                <span class="text-sm font-satoshi-bold text-slate-900">Processing</span>
            </div>
            <h3 class="text-2xl font-satoshi-bold tracking-tight text-slate-900 mt-4">{{ $processingCount ?? 0 }}</h3>
        </x-ui.card>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h5 class="text-lg font-satoshi-bold text-slate-900 mb-0">Kitchen Orders</h5>
        <div class="flex items-center gap-2">
            <button id="kitchen-refresh-btn" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition text-sm font-satoshi-medium">
                <i class="ri-refresh-line"></i> Check New
            </button>
            <a href="{{ route('kitchen.history') }}" class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium">
                <i class="ri-history-line"></i> History
            </a>
        </div>
    </div>

    {{-- Order Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="order-container">
        @forelse($orders as $order)
            <x-ui.card class="p-5 hover:shadow-xl transition-all duration-200" x-data="kitchenOrder({{ $order->id }})">
                <div class="flex items-start justify-between mb-3">
                    <div class="min-w-0">
                        <h6 class="font-satoshi-bold text-slate-900 truncate">#{{ $order->code_invoice }}</h6>
                        <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded-full font-satoshi-medium flex-shrink-0
                        {{ $order->status_order === 'pending' ? 'bg-slate-100 text-slate-600' : '' }}
                        {{ $order->status_order === 'processing' ? 'bg-slate-900 text-white' : '' }}
                        {{ $order->status_order === 'completed' ? 'bg-emerald-50 text-emerald-600' : '' }}
                        {{ $order->status_order === 'cancelled' ? 'bg-rose-50 text-rose-600' : '' }}">
                        {{ ucfirst($order->status_order) }}
                    </span>
                </div>

                <div class="space-y-1 text-sm">
                    <p><span class="text-slate-500">Outlet:</span> {{ $order->outlet?->name ?? '-' }}</p>
                    <p><span class="text-slate-500">Type:</span>
                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-satoshi-semibold bg-slate-100 text-slate-600">
                            {{ $order->order_type === 'takeaway' ? 'Take Away' : 'Dine In' }}
                        </span>
                    </p>
                    <p><span class="text-slate-500">Table:</span> {{ $order->table?->number_table ?? '-' }}</p>
                    <p><span class="text-slate-500">Customer:</span> {{ $order->customer_name ?? '-' }}</p>
                    <p><span class="text-slate-500">Items:</span> {{ $order->items->count() }}</p>
                </div>

                {{-- Item List with Notes & Status --}}
                <div class="mt-3 pt-3 border-t border-slate-100 space-y-2">
                    @foreach($order->items as $item)
                        <div class="flex items-center justify-between text-xs gap-2">
                            <div class="min-w-0">
                                <span class="font-satoshi-medium">{{ $item->product_name }}</span>
                                <span class="text-slate-400">x{{ $item->quantity }}</span>
                                @if($item->notes)
                                    <span class="block italic text-[10px] text-slate-500"><i class="ri-sticky-note-line"></i> {{ $item->notes }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-satoshi-medium
                                    {{ $item->kitchen_status === 'pending' ? 'bg-slate-100 text-slate-600' : '' }}
                                    {{ $item->kitchen_status === 'cooking' ? 'bg-slate-900 text-white' : '' }}
                                    {{ $item->kitchen_status === 'ready' ? 'bg-emerald-50 text-emerald-600' : '' }}
                                    {{ $item->kitchen_status === 'served' ? 'bg-slate-100 text-slate-500' : '' }}">
                                    {{ ucfirst($item->kitchen_status) }}
                                </span>
                                @if($item->kitchen_status === 'pending')
                                    <button type="button" @click="updateItemStatus({{ $item->id }}, 'cooking')"
                                            class="px-1.5 py-0.5 bg-slate-900 text-white text-[10px] rounded hover:bg-slate-800 transition cursor-pointer">
                                        Cook
                                    </button>
                                @endif
                                @if($item->kitchen_status === 'cooking')
                                    <button type="button" @click="updateItemStatus({{ $item->id }}, 'ready')"
                                            class="px-1.5 py-0.5 bg-slate-900 text-white text-[10px] rounded hover:bg-slate-800 transition cursor-pointer">
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
                            <button type="button" @click="updateStatus('processing')"
                                    class="px-3 py-1.5 bg-slate-900 text-white text-xs rounded-lg hover:bg-slate-800 transition cursor-pointer">
                                Process All
                            </button>
                        @endif

                        @if($order->status_order === 'processing')
                            <button type="button" @click="updateStatus('completed')"
                                    class="px-3 py-1.5 bg-slate-900 text-white text-xs rounded-lg hover:bg-slate-800 transition cursor-pointer">
                                Complete All
                            </button>
                        @endif

                        @if($order->status_order === 'pending' || $order->status_order === 'processing')
                            <button type="button" @click="updateStatus('cancelled')"
                                    class="px-3 py-1.5 bg-rose-500 text-white text-xs rounded-lg hover:bg-rose-600 transition cursor-pointer">
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
            </x-ui.card>
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
<script>
    // Notifikasi memakai toast standar project (x-ui.notification / createToast),
    // BUKAN SweetAlert2 — konsisten dengan halaman Product.
    function kitchenOrder(orderId) {
        return {
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
                        createToast('success', data.message);
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        createToast('error', data.message || 'Failed to update item status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    createToast('error', 'Failed to update item status');
                });
            }
        };
    }

    // ===================== KITCHEN NOTIFICATION =====================
    let lastCheck = new Date().toISOString();

    function checkNewOrders(showEmptyNotice = true) {
        fetch('/kitchen/new-orders?last_check=' + encodeURIComponent(lastCheck))
            .then(res => res.json())
            .then(data => {
                if (data.count > 0) {
                    createToast('info', data.count + ' new order(s) received — refreshing...');
                    setTimeout(() => location.reload(), 1500);
                } else if (showEmptyNotice) {
                    createToast('info', 'No new orders since last check');
                }
                lastCheck = new Date().toISOString();
            })
            .catch(error => {
                console.error('Error checking new orders:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const refreshBtn = document.getElementById('kitchen-refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => checkNewOrders(true));
        }
    });

    // Auto check setiap 30 detik (tanpa notifikasi kosong agar tidak berisik)
    setInterval(() => checkNewOrders(false), 30000);
</script>
@endpush
