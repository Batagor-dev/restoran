<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Order;
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

        /*
         * ==================================================
         * KPI PENJUALAN (otomatis ter-scope ke outlet aktif
         * lewat global scope BelongsToOutlet pada Order)
         * ==================================================
         */
        $todayQuery = Order::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);

        $data['revenue_today'] = (clone $todayQuery)
            ->whereIn('status_order', ['pending', 'processing', 'completed'])
            ->sum('grand_total');

        $data['orders_today'] = (clone $todayQuery)->count();

        $data['kitchen_pending'] = Order::whereIn('status_order', ['pending', 'processing'])->count();

        $data['completed_today'] = (clone $todayQuery)->where('status_order', 'completed')->count();

        // Tren 7 hari terakhir untuk grafik
        $data['sales_chart'] = collect(range(6, 0))->map(function ($daysAgo) {
            $day = now()->subDays($daysAgo);

            return [
                'label' => $day->format('d M'),
                'revenue' => (float) Order::whereBetween('created_at', [
                    $day->copy()->startOfDay(),
                    $day->copy()->endOfDay(),
                ])
                    ->where('status_order', '!=', 'cancelled')
                    ->sum('grand_total'),
                'orders' => Order::whereBetween('created_at', [
                    $day->copy()->startOfDay(),
                    $day->copy()->endOfDay(),
                ])->count(),
            ];
        });

        // Transaksi terbaru
        $data['recent_orders'] = Order::with(['outlet', 'cashier', 'table'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', $data);
    }
}
