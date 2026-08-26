<?php

namespace App\DataTables;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CustomerDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('is_active', function ($row) {
                return $row->is_active
                    ? '<span class="badge bg-emerald-50 text-emerald-600">Active</span>'
                    : '<span class="badge bg-rose-50 text-rose-600">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $editBtn = '<a href="'.route('customers.edit', $row->uuid).'" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 transition-colors" title="Edit">
                                <i class="ri-edit-line text-lg"></i>
                            </a>';
                $deleteBtn = '<form action="'.route('customers.destroy', $row->uuid).'" method="POST" style="display:inline">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-rose-500 hover:bg-rose-50 transition-colors" onclick="return confirm(\'Are you sure?\')" title="Delete">
                                    <i class="ri-delete-bin-line text-lg"></i>
                                </button>
                            </form>';

                return $editBtn.' '.$deleteBtn;
            })
            ->rawColumns(['is_active', 'action']);
    }

    public function query(Customer $model): QueryBuilder
    {
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('customer-table')
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
            Column::make('name')->title('Name'),
            Column::make('email')->title('Email'),
            Column::make('phone')->title('Phone'),
            Column::make('address')->title('Address'),
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
        return 'Customer_'.date('YmdHis');
    }
}
