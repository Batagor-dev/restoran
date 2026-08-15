<?php

namespace App\DataTables;

use App\Models\ProductStock;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductStockDataTable extends DataTable
{
    /**
     * Build DataTable class.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('product_name', function ($row) {
                return $row->product->name ?? '-';
            })
            ->addColumn('outlet_name', function ($row) {
                return $row->outlet->name ?? '-';
            })
            ->editColumn('quantity', function ($row) {
                return number_format($row->quantity);
            })
            ->addColumn('action', function ($row) {
                // Edit button
                if (auth()->user()->can('Product Stock Update')) {
                    $edit = '<a href="' . route('product-stocks.edit', $row->uuid) . '"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 transition-colors font-satoshi-medium"
                            data-bs-toggle="tooltip" title="Edit">
                            <i class="ri ri-edit-line text-lg"></i>
                         </a>';
                } else {
                    $edit = '';
                }

                // Delete button
                if (auth()->user()->can('Product Stock Delete')) {
                    $delete = '
                    <form action="' . route('product-stocks.destroy', $row->uuid) . '"
                          method="POST" style="display:inline-block;" class="delete-form m-0">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 transition-colors delete-btn font-satoshi-medium"
                            data-id="' . $row->uuid . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ri ri-delete-bin-line text-lg"></i>
                        </button>
                    </form>';
                } else {
                    $delete = '';
                }

                return '<div class="flex items-center space-x-2 justify-center">' . $edit . ' ' . $delete . '</div>';
            })
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(ProductStock $model)
    {
        return $model->newQuery()->with(['product', 'outlet']);
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('product-stocks-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->responsive(true)
            ->addTableClass('min-w-full divide-y divide-slate-200 overflow-hidden bg-white text-sm font-satoshi-medium text-slate-700')
            ->parameters([
                'dom' => '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 font-satoshi-medium"lf>' .
                    '<"overflow-x-auto w-full"tr>' .
                    '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-4 font-satoshi-medium text-slate-500 text-sm"ip>',
                'language' => [
                    'search' => '<span class="text-slate-600 mr-2 font-satoshi-medium">Search:</span>',
                    'searchPlaceholder' => 'Search stock...',
                    'lengthMenu' => '<span class="text-slate-600 mr-2 font-satoshi-medium">Show</span> _MENU_ <span class="text-slate-600 ml-2 font-satoshi-medium">Entries</span>',
                    'info' => 'Showing _START_ to _END_ of _TOTAL_ entries',
                    'paginate' => [
                        'first' => '<i class="ri-arrow-left-double-line text-lg"></i>',
                        'previous' => '<i class="ri-arrow-left-s-line text-lg"></i>',
                        'next' => '<i class="ri-arrow-right-s-line text-lg"></i>',
                        'last' => '<i class="ri-arrow-right-double-line text-lg"></i>',
                    ],
                ],
            ]);
    }

    /**
     * Get columns.
     */
    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false)->width(40)->addClass('text-center px-4 py-3 bg-slate-50 font-satoshi-medium text-slate-500 border-b border-slate-200'),
            Column::make('product_name')->title('Product Name')->orderable(false)->addClass('px-4 py-3 border-b border-slate-200 text-slate-900 font-semibold'),
            Column::make('outlet_name')->title('Outlet')->orderable(false)->addClass('px-4 py-3 border-b border-slate-200 text-slate-700'),
            Column::make('quantity')->title('Quantity')->addClass('px-4 py-3 border-b border-slate-200 text-slate-900 font-semibold'),
            Column::computed('action')
                ->title('Action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center px-4 py-3 border-b border-slate-200'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'ProductStock_' . date('YmdHis');
    }
}
