<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $data['total_articles'] = Article::count();
        $data['total_categories'] = ArticleCategory::count();
        $data['total_users'] = User::count();

        // Ambil artikel terbaru dari database
        $data['recent_articles'] = Article::with(['category', 'author'])
            ->latest()
            ->take(5)
            ->get();

        // Penulis Teraktif
        $data['top_authors'] = User::withCount('articles')
            ->orderBy('articles_count', 'desc')
            ->take(3)
            ->get();

        return view('dashboard.index', $data);
    }
}
