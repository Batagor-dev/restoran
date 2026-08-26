@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Transactions';
    $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName());
    $canRefund = auth()->user()->can('Transaction Refund');
    $canVoid = auth()->user()->can('Transaction Void');
@endphp

@extends('layouts.backend.main')

@section('title', 'Transaction Management')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
<div class="space-y-8 pb-12">
    <!-- Header + Report Link -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h5 class="text-lg font-satoshi-bold text-slate-900 mb-0">Riwayat Transaksi POS</h5>
            <p class="text-sm text-slate-500 mt-1">Kelola dan pantau seluruh transaksi pada outlet aktif.</p>
        </div>
        @can('Transaction Report')
            <a href="{{ route('transactions.report') }}"
               class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition text-sm font-satoshi-medium self-start sm:self-auto">
                <i class="ri-bar-chart-box-line mr-1"></i> Revenue Report
            </a>
        @endcan
    </div>

    <!-- Filter Bar -->
    <x-ui.card class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            {{-- Start Date --}}
            <div>
                <label for="filter-start-date" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">Start Date</label>
                <input type="date" id="filter-start-date" value="{{ request('start_date') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-satoshi-medium text-slate-900 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200 transition">
            </div>

            {{-- End Date --}}
            <div>
                <label for="filter-end-date" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">End Date</label>
                <input type="date" id="filter-end-date" value="{{ request('end_date') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-satoshi-medium text-slate-900 focus:border-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-200 transition">
            </div>

            {{-- Cashier --}}
            <div>
                <label for="filter-cashier" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">Cashier</label>
                <select id="filter-cashier" class="transaction-filter w-full">
                    <option value="">All Cashiers</option>
                    @foreach($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" {{ request('cashier_id') == $cashier->id ? 'selected' : '' }}>
                            {{ $cashier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Outlet (Super Admin/Owner only) --}}
            @if($outlets->count())
                <div>
                    <label for="filter-outlet" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">Outlet</label>
                    <select id="filter-outlet" class="transaction-filter w-full">
                        <option value="">Active Outlet Only</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                {{ $outlet->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Payment Method --}}
            <div>
                <label for="filter-payment" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">Payment</label>
                <select id="filter-payment" class="transaction-filter w-full">
                    <option value="">All Methods</option>
                    @foreach(['cash', 'qris', 'debit', 'credit'] as $method)
                        <option value="{{ $method }}" {{ request('payment_method') === $method ? 'selected' : '' }}>
                            {{ strtoupper($method) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label for="filter-status" class="block text-xs font-satoshi-medium text-slate-500 mb-1.5">Status</label>
                <select id="filter-status" class="transaction-filter w-full">
                    <option value="">All Status</option>
                    <option value="normal" {{ request('status_transaction') === 'normal' ? 'selected' : '' }}>Success</option>
                    <option value="refunded" {{ request('status_transaction') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    <option value="voided" {{ request('status_transaction') === 'voided' ? 'selected' : '' }}>Voided</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 mt-4">
            <button type="button" id="reset-filters"
                    class="px-4 py-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium">
                <i class="ri-refresh-line mr-1"></i> Reset Filter
            </button>

            <button type="button" id="export-csv"
                    class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition text-sm font-satoshi-medium">
                <i class="ri-file-excel-2-line mr-1"></i> Export Excel
            </button>

            <a href="{{ route('transactions.export', ['format' => 'pdf']) }}" target="_blank"
               id="export-pdf"
               class="px-4 py-2 border border-slate-900 text-slate-900 rounded-lg hover:bg-slate-50 transition text-sm font-satoshi-medium">
                <i class="ri-file-pdf-2-line mr-1"></i> Export PDF
            </a>
        </div>
    </x-ui.card>

    <!-- Table -->
    <x-ui.card>
        <div>
            {!! $dataTable->table(['width' => '100%']) !!}
        </div>
    </x-ui.card>
</div>
@endsection

@push('scripts')
    {{-- Render DataTable --}}
    {!! $dataTable->scripts() !!}

    <script>
    $(document).ready(function () {
        $('.transaction-filter').select2({
            width: '100%',
            placeholder: 'All',
            allowClear: true
        });

        // Debounce: gabungkan banyak trigger change menjadi SATU request ajax,
        // mencegah request saling abort -> DataTables Ajax error.
        let drawTimer = null;

        function reloadTable(delay = 300) {
            clearTimeout(drawTimer);
            drawTimer = setTimeout(function () {
                $('#transactions-table').DataTable().draw();
            }, delay);
        }

        // Terapkan filter -> reload DataTable
        $('.transaction-filter, #filter-start-date, #filter-end-date').on('change', function () {
            reloadTable();
        });

        $('#reset-filters').on('click', function () {
            $('#filter-start-date').val('');
            $('#filter-end-date').val('');
            $('.transaction-filter').val(null).trigger('change');
            reloadTable(350);
        });

        // Export CSV mengikuti filter yang sedang aktif
        $('#export-csv').on('click', function () {
            const params = new URLSearchParams();
            const start = $('#filter-start-date').val();
            const end = $('#filter-end-date').val();

            if (start) params.append('start_date', start);
            if (end) params.append('end_date', end);
            if ($('#filter-cashier').val()) params.append('cashier_id', $('#filter-cashier').val());
            if ($('#filter-outlet').val()) params.append('outlet_id', $('#filter-outlet').val());
            if ($('#filter-payment').val()) params.append('payment_method', $('#filter-payment').val());
            if ($('#filter-status').val()) params.append('status_transaction', $('#filter-status').val());

            window.location.href = '{{ route('transactions.export') }}?format=csv' + (params.toString() ? '&' + params.toString() : '');
        });
    });

    // ---- Refund & Void (konfirmasi ala halaman Product) ----
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

    $(document).on('click', '.refund-btn', function () {
        const uuid = $(this).data('uuid');
        const invoice = $(this).data('invoice');

        Swal.fire({
            title: 'Refund ' + invoice + '?',
            text: 'Stock will be restored and the transaction will be marked as refunded.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                promptReason('Refund Reason', '/transactions/' + uuid + '/refund', 'Refund Transaction');
            }
        });
    });

    $(document).on('click', '.void-btn', function () {
        const uuid = $(this).data('uuid');
        const invoice = $(this).data('invoice');

        Swal.fire({
            title: 'Void ' + invoice + '?',
            text: 'The transaction will be cancelled and stock restored.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, void it',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                promptReason('Void Reason', '/transactions/' + uuid + '/void', 'Void Transaction');
            }
        });
    });
    </script>
@endpush
