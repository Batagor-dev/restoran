<?php

namespace App\DataTables;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockMovementDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('product_name', function ($movement) {
                return $movement->productStock->product->name ?? '-';
            })
            ->addColumn('outlet_name', function ($movement) {
                return $movement->productStock->outlet->name ?? '-';
            })
            ->editColumn('movement_type', function ($movement) {
                $badge = [
                    'in' => 'success',
                    'out' => 'danger',
                    'adjustment' => 'warning',
                    'return' => 'info'
                ][$movement->movement_type] ?? 'secondary';

                return '<span class="badge bg-' . $badge . '">' . ucfirst($movement->movement_type) . '</span>';
            })
            ->editColumn('created_at', function ($movement) {
                return $movement->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function ($movement) {
                return view('stock-movements.action', compact('movement'));
            })
            ->rawColumns(['movement_type', 'action']);
    }

    public function query(StockMovement $model): QueryBuilder
    {
        return $model->newQuery()->with(['productStock.product', 'productStock.outlet']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('stock-movement-table')
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
            Column::make('product_name')->title('Product')->orderable(false),
            Column::make('outlet_name')->title('Outlet')->orderable(false),
            Column::make('movement_type')->title('Type'),
            Column::make('quantity')->title('Quantity'),
            Column::make('stock_before')->title('Before'),
            Column::make('stock_after')->title('After'),
            Column::make('notes')->title('Notes'),
            Column::make('created_at')->title('Date'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(150)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'StockMovement_' . date('YmdHis');
    }
}