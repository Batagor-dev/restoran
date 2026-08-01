<?php

namespace App\DataTables;

use App\Models\CustomerPromo;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CustomerPromoDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('discount_value', function ($row) {
                if ($row->type == 'percentage') {
                    return $row->discount_value . '%';
                }
                return 'Rp ' . number_format($row->discount_value, 0, ',', '.');
            })
            ->editColumn('minimum_purchase', function ($row) {
                if ($row->minimum_purchase) {
                    return 'Rp ' . number_format($row->minimum_purchase, 0, ',', '.');
                }
                return '-';
            })
            ->editColumn('start_date', function ($row) {
                return $row->start_date->format('d M Y H:i');
            })
            ->editColumn('end_date', function ($row) {
                return $row->end_date->format('d M Y H:i');
            })
            ->editColumn('is_active', function ($row) {
                return $row->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $editBtn = '<a href="' . route('customer-promos.edit', $row->uuid) . '" class="btn btn-sm btn-primary">
                    <i class="ri-edit-line"></i>
                </a>';
                $deleteBtn = '<form action="' . route('customer-promos.destroy', $row->uuid) . '" method="POST" style="display:inline">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </form>';
                return $editBtn . ' ' . $deleteBtn;
            })
            ->rawColumns(['is_active', 'action']);
    }

    public function query(CustomerPromo $model): QueryBuilder
    {
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('customer-promo-table')
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
            Column::make('name')->title('Promo Name'),
            Column::make('scope')->title('Scope'),
            Column::make('type')->title('Type'),
            Column::make('discount_value')->title('Discount'),
            Column::make('minimum_purchase')->title('Min Purchase'),
            Column::make('start_date')->title('Start Date'),
            Column::make('end_date')->title('End Date'),
            Column::make('is_active')->title('Status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(150)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'CustomerPromo_' . date('YmdHis');
    }
}
