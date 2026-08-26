<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TransactionDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d M Y H:i');
            })
            ->addColumn('cashier_name', fn ($row) => $row->cashier->name ?? '-')
            ->addColumn('customer_label', function ($row) {
                $name = $row->customer_name ?? '-';

                return $row->customer_id
                    ? $name.' <span class="badge bg-emerald-50 text-emerald-600">Member</span>'
                    : $name;
            })
            ->editColumn('payment_method', function ($row) {
                $badges = [
                    'cash' => 'bg-slate-100 text-slate-600',
                    'qris' => 'bg-slate-900 text-white',
                    'debit' => 'bg-slate-100 text-slate-600',
                    'credit' => 'bg-slate-100 text-slate-600',
                ];

                return '<span class="badge '.($badges[$row->payment_method] ?? 'bg-slate-100 text-slate-600').'">'
                    .strtoupper($row->payment_method).'</span>';
            })
            ->editColumn('order_type', fn ($row) => $row->order_type === 'takeaway' ? 'Take Away' : 'Dine In')
            ->editColumn('grand_total', function ($row) {
                if ($row->status_transaction === 'voided') {
                    return '<span class="text-slate-400 line-through">Rp '
                        .number_format($row->grand_total, 0, ',', '.').'</span>';
                }

                return 'Rp '.number_format($row->grand_total, 0, ',', '.');
            })
            ->editColumn('status_transaction', function ($row) {
                $map = [
                    'normal' => '<span class="badge bg-emerald-50 text-emerald-600">Success</span>',
                    'refunded' => '<span class="badge bg-rose-50 text-rose-600">Refunded</span>',
                    'voided' => '<span class="badge bg-slate-100 text-slate-500">Voided</span>',
                ];

                return $map[$row->status_transaction] ?? '-';
            })
            ->addColumn('action', function ($row) {
                $detail = '<a href="'.route('transactions.show', $row->uuid)
                    .'" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 transition-colors" title="Detail">'
                    .'<i class="ri-eye-line text-lg"></i></a>';

                $print = '';
                if (auth()->user()->can('Transaction Access')) {
                    $print = '<a href="'.route('transactions.receipt', $row->uuid).'" target="_blank"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 transition-colors" title="Print Receipt">'
                            .'<i class="ri-printer-line text-lg"></i></a>';
                }

                $refund = '';
                if ($row->status_transaction === 'normal' && auth()->user()->can('Transaction Refund')) {
                    $refund = '<button type="button" class="refund-btn inline-flex items-center justify-center w-8 h-8 rounded-full text-rose-500 hover:bg-rose-50 transition-colors"
                            data-uuid="'.$row->uuid.'" data-invoice="'.$row->code_invoice.'" title="Refund">
                            <i class="ri-arrow-go-back-line text-lg"></i></button>';
                }

                $void = '';
                if ($row->status_transaction === 'normal' && auth()->user()->can('Transaction Void')) {
                    $void = '<button type="button" class="void-btn inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-500 hover:bg-slate-100 transition-colors"
                            data-uuid="'.$row->uuid.'" data-invoice="'.$row->code_invoice.'" title="Void">
                            <i class="ri-close-circle-line text-lg"></i></button>';
                }

                return '<div class="flex items-center space-x-1 justify-center">'.$detail.$print.$refund.$void.'</div>';
            })
            ->rawColumns(['payment_method', 'grand_total', 'status_transaction', 'customer_label', 'action'])
            ->setRowId(fn ($row) => 'order-'.$row->uuid);
    }

    public function query(Order $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['cashier', 'outlet', 'table'])
            ->select('orders.*');

        // Filter tanggal
        if (request()->filled('start_date')) {
            $query->whereDate('orders.created_at', '>=', request('start_date'));
        }
        if (request()->filled('end_date')) {
            $query->whereDate('orders.created_at', '<=', request('end_date'));
        }

        // Filter kasir (guard numeric agar nilai sampah tidak menyebabkan SQL error)
        if (request()->filled('cashier_id') && is_numeric(request('cashier_id'))) {
            $query->where('orders.cashier_id', (int) request('cashier_id'));
        }

        // Filter outlet (hanya efektif bagi Super Admin/Owner yang boleh multi-outlet;
        // karyawan sudah terkunci lewat global scope BelongsToOutlet)
        if (request()->filled('outlet_id') && is_numeric(request('outlet_id'))) {
            $query->where('orders.outlet_id', (int) request('outlet_id'));
        }

        // Filter payment method
        if (in_array(request('payment_method'), ['cash', 'qris', 'debit', 'credit'])) {
            $query->where('orders.payment_method', request('payment_method'));
        }

        // Filter status transaksi
        if (in_array(request('status_transaction'), ['normal', 'refunded', 'voided'])) {
            $query->where('orders.status_transaction', request('status_transaction'));
        }

        // Global search dibatasi ke invoice & nama customer
        if (request()->filled('search') && request('search')['value'] ?? false) {
            $keyword = '%'.request('search')['value'].'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('orders.code_invoice', 'ILIKE', $keyword)
                    ->orWhere('orders.customer_name', 'ILIKE', $keyword);
            });
        }

        return $query->orderBy('orders.created_at', 'desc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('transactions-table')
            ->columns($this->getColumns())
            ->ajax([
                'url' => route('transactions.index'),
                'type' => 'GET',
                // Pola standar DataTables: data sebagai fungsi agar nilai
                // filter dibaca live dari DOM setiap kali draw.
                'data' => 'function (d) {
                    d.start_date = $("#filter-start-date").val() || "";
                    d.end_date = $("#filter-end-date").val() || "";
                    d.cashier_id = $("#filter-cashier").val() || "";
                    d.outlet_id = $("#filter-outlet").val() || "";
                    d.payment_method = $("#filter-payment").val() || "";
                    d.status_transaction = $("#filter-status").val() || "";
                }',
                'error' => 'function () {
                    if (window.createToast) { createToast("error", "Failed to load transactions"); }
                }',
            ])
            ->orderBy(2)
            ->responsive(true)
            ->addTableClass('min-w-full divide-y divide-slate-200 overflow-hidden bg-white text-sm font-satoshi-medium text-slate-700')
            ->parameters([
                'dom' => '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 font-satoshi-medium"lf>'
                    .'<"overflow-x-auto w-full"tr>'
                    .'<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-4 font-satoshi-medium text-slate-500 text-sm"ip>',
                'language' => [
                    'search' => '<span class="text-slate-600 mr-2 font-satoshi-medium">Search:</span>',
                    'searchPlaceholder' => 'Search invoice / customer...',
                    'lengthMenu' => '<span class="text-slate-600 mr-2 font-satoshi-medium">Show</span> _MENU_ <span class="text-slate-600 ml-2 font-satoshi-medium">Entries</span>',
                    'info' => 'Showing _START_ to _END_ of _TOTAL_ transactions',
                    'paginate' => [
                        'first' => '<i class="ri-arrow-left-double-line text-lg"></i>',
                        'previous' => '<i class="ri-arrow-left-s-line text-lg"></i>',
                        'next' => '<i class="ri-arrow-right-s-line text-lg"></i>',
                        'last' => '<i class="ri-arrow-right-double-line text-lg"></i>',
                    ],
                ],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false)->width(40)
                ->addClass('px-4 py-3 text-center'),
            Column::make('code_invoice')->title('Invoice')
                ->addClass('px-4 py-3 font-satoshi-semibold text-slate-900'),
            Column::make('created_at')->title('Date')->addClass('px-4 py-3'),
            Column::make('cashier_name')->title('Cashier')->orderable(false)->addClass('px-4 py-3'),
            Column::make('customer_label')->title('Customer')->orderable(false)->addClass('px-4 py-3'),
            Column::make('order_type')->title('Type')->orderable(false)->addClass('px-4 py-3'),
            Column::make('payment_method')->title('Payment')->orderable(false)->addClass('px-4 py-3 text-center'),
            Column::make('grand_total')->title('Total')->searchable(false)->addClass('px-4 py-3 font-semibold'),
            Column::make('status_transaction')->title('Status')->orderable(false)->searchable(false)
                ->addClass('px-4 py-3 text-center'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(160)
                ->addClass('text-center px-4 py-3'),
        ];
    }

    protected function filename(): string
    {
        return 'Transactions_'.date('YmdHis');
    }
}
