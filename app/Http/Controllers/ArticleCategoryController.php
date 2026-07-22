<?php

namespace App\Http\Controllers;

use App\DataTables\ArticleCategoryDataTable;
use App\Http\Requests\StoreArticleCategoryRequest;
use App\Http\Requests\UpdateArticleCategoryRequest;
use App\Models\ArticleCategory;
use Illuminate\Http\Response;

class ArticleCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(ArticleCategoryDataTable $dataTable)
    {
        return $dataTable->render('article_categories.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->data['action'] = '/article_categories';

        return view('article_categories.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StoreArticleCategoryRequest $request)
    {
        ArticleCategory::create($request->all());

        return redirect('article_categories')->with('success', 'New Category has been created!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(ArticleCategory $articleCategory)
    {
        $this->data['article_categories_data'] = $articleCategory;
        $this->data['action'] = '/article_categories/'.$articleCategory->slug;
        $this->data['model'] = $articleCategory;

        return view('article_categories.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(UpdateArticleCategoryRequest $request, ArticleCategory $articleCategory)
    {
        $articleCategory->update($request->all());

        return redirect()
            ->route('article_categories.index')
            ->with('success', 'Category has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(ArticleCategory $articleCategory)
    {
        $articleCategory->update(['status' => '0']);

        return redirect('/article_categories')->with('success', 'Category has been Deleted!');
    }
}
