<?php

namespace App\DataTables;

use App\Models\Promo;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PromoDataTable extends DataTable
{
    /**
     * Build DataTable class.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('scope', function ($row) {
                $badges = [
                    'order' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-satoshi-medium bg-blue-100 text-blue-800">Order</span>',
                    'product' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-satoshi-medium bg-purple-100 text-purple-800">Product</span>',
                    'category_product' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-satoshi-medium bg-amber-100 text-amber-800">Category Product</span>',
                ];

                return $badges[$row->scope] ?? $row->scope;
            })
            ->addColumn('discount', function ($row) {
                if ($row->type === 'percentage') {
                    return number_format($row->discount_value, 0).'%';
                }

                return 'Rp '.number_format($row->discount_value, 0, ',', '.');
            })
            ->addColumn('min_purchase', function ($row) {
                return $row->minimum_purchase ? 'Rp '.number_format($row->minimum_purchase, 0, ',', '.') : '-';
            })
            ->addColumn('validity', function ($row) {
                $start = $row->start_date ? $row->start_date->format('d M Y H:i') : '-';
                $end = $row->end_date ? $row->end_date->format('d M Y H:i') : '-';

                return $start.' - '.$end;
            })
            ->addColumn('is_active', function ($row) {
                if ($row->is_active) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-satoshi-medium bg-emerald-100 text-emerald-800">Active</span>';
                }

                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-satoshi-medium bg-slate-100 text-slate-600">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $edit = '';
                $delete = '';

                if (auth()->user()->can('Promo Update')) {
                    $edit = '<a href="'.route('promo.edit', $row->uuid).'"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 transition-colors font-satoshi-medium"
                                data-bs-toggle="tooltip" title="Edit">
                                <i class="ri ri-edit-line text-lg"></i>
                             </a>';
                }

                if (auth()->user()->can('Promo Delete')) {
                    $delete = '
                        <form action="'.route('promo.destroy', $row->uuid).'"
                              method="POST" style="display:inline-block;" class="delete-form m-0">
                            '.csrf_field().method_field('DELETE').'
                            <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 transition-colors delete-btn font-satoshi-medium"
                                data-id="'.$row->uuid.'"
                                data-bs-toggle="tooltip" title="Delete">
                                <i class="ri ri-delete-bin-line text-lg"></i>
                            </button>
                        </form>';
                }

                return '<div class="flex items-center space-x-2 justify-center">'.$edit.' '.$delete.'</div>';
            })
            ->rawColumns(['scope', 'is_active', 'action']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(Promo $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('promo-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->responsive(true)
            ->addTableClass('min-w-full divide-y divide-slate-200 overflow-hidden bg-white text-sm font-satoshi-medium text-slate-700')
            ->parameters([
                'dom' => '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 font-satoshi-medium"lf>'.
                         '<"overflow-x-auto w-full"tr>'.
                         '<"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-4 font-satoshi-medium text-slate-500 text-sm"ip>',
                'language' => [
                    'search' => '<span class="text-slate-600 mr-2 font-satoshi-medium">Search:</span>',
                    'searchPlaceholder' => 'Search promo...',
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
            Column::make('name')->title('Promo Name')->addClass('px-4 py-3 border-b border-slate-200 text-slate-900 font-semibold'),
            Column::make('scope')->title('Scope')->addClass('px-4 py-3 border-b border-slate-200 text-center'),
            Column::make('discount')->title('Discount')->addClass('px-4 py-3 border-b border-slate-200 text-slate-900 font-semibold'),
            Column::make('min_purchase')->title('Min Purchase')->addClass('px-4 py-3 border-b border-slate-200 text-slate-700'),
            Column::make('validity')->title('Validity Period')->addClass('px-4 py-3 border-b border-slate-200 text-slate-700'),
            Column::make('is_active')->title('Status')->addClass('px-4 py-3 border-b border-slate-200 text-center'),
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
        return 'Promo_'.date('YmdHis');
    }
}
