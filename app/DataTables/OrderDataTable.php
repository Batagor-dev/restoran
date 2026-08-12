<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrderDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('outlet_name', function ($row) {
                return $row->outlet->name ?? '-';
            })
            ->addColumn('cashier_name', function ($row) {
                return $row->cashier->name ?? '-';
            })
            ->addColumn('table_number', function ($row) {
                return $row->table->number_table ?? $row->table->table_number ?? '-';
            })
            ->addColumn('customer_name', function ($row) {
                return $row->customer->name ?? $row->customer_name ?? '-';
            })
            ->editColumn('grand_total', function ($row) {
                return 'Rp ' . number_format($row->grand_total, 0, ',', '.');
            })
            ->editColumn('status_order', function ($row) {
                $badge = [
                    'pending' => 'warning',
                    'processing' => 'info',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                ][$row->status_order] ?? 'secondary';

                return '<span class="badge bg-' . $badge . '">' . ucfirst($row->status_order) . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function ($row) {
                $editBtn = '<a href="' . route('orders.edit', $row->uuid) . '" class="btn btn-sm btn-primary">
                                <i class="ri-edit-line"></i>
                            </a>';
                $deleteBtn = '<form action="' . route('orders.destroy', $row->uuid) . '" method="POST" style="display:inline">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>';
                $detailBtn = '<a href="' . route('orders.show', $row->uuid) . '" class="btn btn-sm btn-info">
                                <i class="ri-eye-line"></i>
                            </a>';
                return $detailBtn . ' ' . $editBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['status_order', 'action']);
    }

    public function query(Order $model): QueryBuilder
    {
        return $model->newQuery()->with(['outlet', 'cashier', 'table', 'customer']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('order-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->orderable(false)->searchable(false)->width(50),
            Column::make('code_invoice')->title('Invoice'),
            Column::make('outlet_name')->title('Outlet')->orderable(false),
            Column::make('cashier_name')->title('Cashier')->orderable(false),
            Column::make('table_number')->title('Table')->orderable(false),
            Column::make('customer_name')->title('Customer')->orderable(false),
            Column::make('grand_total')->title('Total'),
            Column::make('status_order')->title('Status'),
            Column::make('created_at')->title('Date'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(200)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Order_' . date('YmdHis');
    }
}
