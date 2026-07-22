<?php

namespace App\DataTables;

use App\Models\Article;
use Carbon\Carbon;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ArticleDataTable extends DataTable
{
    /**
     * Build DataTable class.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('image', function ($article) {
                return $article->image_path
                    ? '<img src="'.asset('storage/'.$article->image_path).'" class="w-16 h-10 object-cover rounded-lg shadow-sm border border-slate-100 mx-auto">'
                    : '<span class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-satoshi-semibold select-none bg-slate-100 text-slate-600">No Image</span>';
            })
            ->addColumn('category', fn ($article) => $article->category?->name ?? '-')
            ->addColumn('author', fn ($article) => $article->author?->name ?? '-')
            ->addColumn('outlet', fn ($article) => $article->outlet?->name ?? 'Global')
            ->addColumn('published_at', fn ($article) => $article->published_at
                ? Carbon::parse($article->published_at)->isoFormat('dddd, D MMMM Y')
                : '-'
            )
            ->addColumn('action', function ($row) {
                $edit = '';
                $delete = '';

                if (auth()->user()->can('Article Update')) {
                    $edit = '<a href="'.route('article.edit', $row->slug).'"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 transition-colors font-satoshi-medium"
                                data-bs-toggle="tooltip" title="Edit">
                                <i class="ri ri-edit-line text-lg"></i>
                             </a>';
                }

                if (auth()->user()->can('Article Delete')) {
                    $delete = '
                        <form action="'.route('article.destroy', $row->slug).'"
                              method="POST" style="display:inline-block;" class="delete-form m-0">
                            '.csrf_field().method_field('DELETE').'
                            <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-600 hover:bg-slate-100 transition-colors delete-btn font-satoshi-medium"
                                data-id="'.$row->slug.'"
                                data-bs-toggle="tooltip" title="Delete">
                                <i class="ri ri-delete-bin-line text-lg"></i>
                            </button>
                        </form>';
                }

                return '<div class="flex items-center space-x-2 justify-center">'.$edit.' '.$delete.'</div>';
            })
            ->rawColumns(['image', 'action']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(Article $model)
    {
        return $model->newQuery()->with(['category', 'author', 'outlet']);
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('articles-table')
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
                    'searchPlaceholder' => 'Search article...',
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
            Column::make('image')->title('Image')->orderable(false)->searchable(false)->addClass('text-center px-4 py-3 border-b border-slate-200'),
            Column::make('title')->title('Article')->addClass('px-4 py-3 border-b border-slate-200 text-slate-900 font-semibold'),
            Column::make('category')->title('Category')->orderable(false)->addClass('px-4 py-3 border-b border-slate-200 text-slate-500'),
            Column::make('author')->title('Author')->orderable(false)->addClass('px-4 py-3 border-b border-slate-200 text-slate-500'),
            Column::make('outlet')->title('Outlet')->orderable(false)->addClass('px-4 py-3 border-b border-slate-200 text-slate-500'),
            Column::make('published_at')->title('Published At')->addClass('px-4 py-3 border-b border-slate-200 text-slate-500'),
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
        return 'Articles_'.date('YmdHis');
    }
}
