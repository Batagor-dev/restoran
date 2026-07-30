<?php

namespace App\DataTables;

use App\Models\StockMovement;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockMovementDataTable extends DataTable
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
                return $row->productStock->product->name ?? '-';
            })
            ->addColumn('outlet_name', function ($row) {
                return $row->productStock->outlet->name ?? '-';
            })
            ->editColumn('movement_type', function ($row) {
                $type = strtolower($row->movement_type);
                $badges = [
                    'in' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-satoshi-medium bg-emerald-100 text-emerald-800">In</span>',
                    'out' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-satoshi-medium bg-rose-100 text-rose-800">Out</span>',
                    'adjustment' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-satoshi-medium bg-amber-100 text-amber-800">Adjustment</span>',
                    'return' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-satoshi-medium bg-sky-100 text-sky-800">Return</span>',
                ];

                return $badges[$type] ?? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-satoshi-medium bg-slate-100 text-slate-600">' . ucfirst($type) . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '-';
            })
            ->editColumn('quantity', function ($row) {
                return number_format($row->quantity);
            })
            ->editColumn('stock_before', function ($row) {
                return number_format($row->stock_before);
            })
            ->editColumn('stock_after', function ($row) {
                return number_format($row->stock_after);
            })
            ->addColumn('notes', function ($row) {
                return $row->notes ?: '-';
            })
            ->addColumn('action', function ($row) {
                $delete = '';

                if (auth()->user()->can('Stock Movement Delete')) {
                    $delete = '
                        <form action="'.route('stock-movements.destroy', $row->uuid).'"
                              method="POST" style="display:inline-block;" class="delete-form m-0">
                            '.csrf_field().method_field('DELETE').'
                            <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 transition-colors delete-btn font-satoshi-medium"
                                data-id="'.$row->uuid.'"
                                data-bs-toggle="tooltip" title="Delete">
                                <i class="ri ri-delete-bin-line text-lg"></i>
                            </button>
                        </form>';
                }

                return '<div class="flex items-center space-x-2 justify-center">'.$delete.'</div>';
            })
            ->rawColumns(['movement_type', 'action']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(StockMovement $model)
    {
        return $model->newQuery()->with(['productStock.product', 'productStock.outlet']);
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('stock-movements-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(8, 'desc')
            ->responsive(true)
            ->addTableClass('min-w-full divide-y divide-slate-200 overflow-hidden bg-white text-sm font-satoshi-medium text-slate-700')
            ->parameters([
                'dom' => '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 font-satoshi-medium"lf>'.
                         '<"overflow-x-auto w-full"tr>'.
                         '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-4 font-satoshi-medium text-slate-500 text-sm"ip>',
                'language' => [
                    'search' => '<span class="text-slate-600 mr-2 font-satoshi-medium">Search:</span>',
                    'searchPlaceholder' => 'Search movement...',
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
            Column::make('movement_type')->title('Type')->addClass('px-4 py-3 border-b border-slate-200 text-center'),
            Column::make('quantity')->title('Quantity')->addClass('px-4 py-3 border-b border-slate-200 text-slate-900 font-semibold text-center'),
            Column::make('stock_before')->title('Before')->addClass('px-4 py-3 border-b border-slate-200 text-slate-700 text-center'),
            Column::make('stock_after')->title('After')->addClass('px-4 py-3 border-b border-slate-200 text-slate-700 text-center'),
            Column::make('notes')->title('Notes')->addClass('px-4 py-3 border-b border-slate-200 text-slate-700'),
            Column::make('created_at')->title('Date')->addClass('px-4 py-3 border-b border-slate-200 text-slate-700'),
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
        return 'StockMovement_'.date('YmdHis');
    }
}