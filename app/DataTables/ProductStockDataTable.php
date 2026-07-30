<?php

namespace App\DataTables;

use App\Models\ProductStock;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductStockDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('product_name', function ($stock) {
                return $stock->product->name ?? '-';
            })
            ->addColumn('outlet_name', function ($stock) {
                return $stock->outlet->name ?? '-';
            })
            ->editColumn('quantity', function ($stock) {
                return number_format($stock->quantity);
            })
            ->addColumn('action', function ($stock) {
                return view('product-stocks.action', compact('stock'));
            })
            ->rawColumns(['action']);
    }

    public function query(ProductStock $model): QueryBuilder
    {
        return $model->newQuery()->with(['product', 'outlet']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-stock-table')
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
            Column::make('quantity')->title('Quantity'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(150)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'ProductStock_' . date('YmdHis');
    }
}