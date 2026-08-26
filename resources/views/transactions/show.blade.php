@php
    $title = 'Transaction Detail';
    $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $transaction);
@endphp

@extends('layouts.backend.main')

@section('title', $title)
@section('sub_title', $title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
<div class="space-y-8 pb-12">
    <x-ui.card>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h5 class="text-lg font-satoshi-bold text-slate-900 mb-1">#{{ $transaction->code_invoice }}</h5>
                <p class="text-sm text-slate-500">{{ $transaction->created_at->format('d M Y H:i') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 text-xs rounded-full font-satoshi-semibold
                    {{ $transaction->status_transaction === 'normal' ? 'bg-emerald-50 text-emerald-600' : '' }}
                    {{ $transaction->status_transaction === 'refunded' ? 'bg-rose-50 text-rose-600' : '' }}
                    {{ $transaction->status_transaction === 'voided' ? 'bg-slate-100 text-slate-500' : '' }}">
                    @if($transaction->status_transaction === 'normal') Success
                    @elseif($transaction->status_transaction === 'refunded') Refunded
                    @else Voided
                    @endif
                </span>

                @can('Transaction Access')
                    <a href="{{ route('transactions.receipt', $transaction->uuid) }}" target="_blank"
                       class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium">
                        <i class="ri-printer-line mr-1"></i> Print Receipt
                    </a>
                @endcan

                <a href="{{ route('transactions.index') }}"
                   class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium">
                    <i class="ri-arrow-left-line mr-1"></i> Back
                </a>
            </div>
        </div>

        @if($transaction->status_transaction === 'voided')
            <div class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                <p class="font-satoshi-bold text-slate-700 mb-1"><i class="ri-close-circle-line"></i> Transaction Voided</p>
                <p class="text-slate-500">Reason: {{ $transaction->void_reason }}</p>
                <p class="text-slate-500">By {{ $transaction->voidedBy?->name ?? '-' }} at {{ optional($transaction->voided_at)->format('d M Y H:i') }}</p>
            </div>
        @endif

        @if($transaction->status_transaction === 'refunded')
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-sm">
                <p class="font-satoshi-bold text-rose-600 mb-1"><i class="ri-arrow-go-back-line"></i> Transaction Refunded</p>
                <p class="text-rose-500">Reason: {{ $transaction->refund_reason }}</p>
                <p class="text-rose-500">Refund amount: <strong>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</strong></p>
                <p class="text-rose-500">By {{ $transaction->refundedBy?->name ?? '-' }} at {{ optional($transaction->refunded_at)->format('d M Y H:i') }}</p>
            </div>
        @endif

        <!-- Info Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-slate-50 rounded-xl">
            <div>
                <p class="text-xs text-slate-500">Outlet</p>
                <p class="font-satoshi-medium">{{ $transaction->outlet?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Cashier</p>
                <p class="font-satoshi-medium">{{ $transaction->cashier?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Customer</p>
                <p class="font-satoshi-medium">
                    {{ $transaction->customer_name ?? '-' }}
                    @if($transaction->customer_id)
                        <span class="badge bg-emerald-50 text-emerald-600 ml-1">Member</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Table</p>
                <p class="font-satoshi-medium">
                    {{ $transaction->order_type === 'takeaway' ? 'Take Away' : ($transaction->table?->number_table ?? '-') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Payment Method</p>
                <p class="font-satoshi-medium">{{ strtoupper($transaction->payment_method) }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Order Status</p>
                <p class="font-satoshi-medium capitalize">{{ $transaction->status_order }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Voucher</p>
                <p class="font-satoshi-medium">{{ $transaction->promo?->name ?? '-' }}</p>
            </div>
        </div>

        <!-- Items -->
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
                    @foreach($transaction->items as $item)
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
                        <td colspan="3" class="border border-slate-200 px-4 py-2 text-right">Subtotal :</td>
                        <td class="border border-slate-200 px-4 py-2 text-right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border border-slate-200 px-4 py-2 text-right">
                            Voucher {{ $transaction->promo ? '('.$transaction->promo->name.')' : '' }} :
                        </td>
                        <td class="border border-slate-200 px-4 py-2 text-right text-red-500">
                            -Rp {{ number_format($transaction->discount, 0, ',', '.') }}
                        </td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border border-slate-200 px-4 py-2 text-right">Tax (10%) :</td>
                        <td class="border border-slate-200 px-4 py-2 text-right">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="border border-slate-200 px-4 py-2 text-right text-lg">Grand Total :</td>
                        <td class="border border-slate-200 px-4 py-2 text-right text-lg text-emerald-600">
                            Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}
                        </td>
                        <td class="border border-slate-200 px-4 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Refund / Void Actions -->
        @if($transaction->status_transaction === 'normal')
            <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                @can('Transaction Refund')
                    <button type="button" id="refund-btn"
                            data-uuid="{{ $transaction->uuid }}" data-invoice="{{ $transaction->code_invoice }}"
                            class="px-4 py-2 bg-rose-500 text-white rounded-lg hover:bg-rose-600 transition text-sm font-satoshi-medium cursor-pointer">
                        <i class="ri-arrow-go-back-line mr-1"></i> Refund Transaction
                    </button>
                @endcan

                @can('Transaction Void')
                    <button type="button" id="void-btn"
                            data-uuid="{{ $transaction->uuid }}" data-invoice="{{ $transaction->code_invoice }}"
                            class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium cursor-pointer">
                        <i class="ri-close-circle-line mr-1"></i> Void Transaction
                    </button>
                @endcan
            </div>
        @endif
    </x-ui.card>
</div>
@endsection

@push('scripts')
    @if(session('success'))
        <script>
            Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}" });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({ icon: 'error', title: 'Error', text: "{{ session('error') }}" });
        </script>
    @endif

    <script>
    function promptReason(title, url, confirmText) {
        Swal.fire({
            title: title,
            input: 'textarea',
            inputPlaceholder: 'Enter reason (min. 3 characters)...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0f172a',
            cancelButtonColor: '#64748b',
            confirmButtonText: confirmText,
            cancelButtonText: 'Batal',
            preConfirm: (value) => {
                if (!value || value.trim().length < 3) {
                    Swal.showValidationMessage('Reason is required (min. 3 characters)');
                }
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.innerHTML =
                    '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">' +
                    '<input type="hidden" name="reason" value="' + result.value.trim().replace(/"/g, '&quot;') + '">';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    $(document).on('click', '#refund-btn', function () {
        promptReason(
            'Refund ' + $(this).data('invoice') + '?',
            '/transactions/' + $(this).data('uuid') + '/refund',
            'Refund Transaction'
        );
    });

    $(document).on('click', '#void-btn', function () {
        promptReason(
            'Void ' + $(this).data('invoice') + '?',
            '/transactions/' + $(this).data('uuid') + '/void',
            'Void Transaction'
        );
    });
    </script>
@endpush
