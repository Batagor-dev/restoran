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
        $this->data['total_articles'] = Article::count();
        $this->data['total_categories'] = ArticleCategory::count();
        $this->data['total_users'] = User::count();

        // Ambil artikel terbaru dari database
        $this->data['recent_articles'] = Article::with(['category', 'author'])
            ->latest()
            ->take(5)
            ->get();

        // Penulis Teraktif
        $this->data['top_authors'] = User::withCount('articles')
            ->orderBy('articles_count', 'desc')
            ->take(3)
            ->get();

        /*
         * ==================================================
         * KPI PENJUALAN (otomatis ter-scope ke outlet aktif
         * lewat global scope BelongsToOutlet pada Order)
         * ==================================================
         */
        $todayStats = Order::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->selectRaw("
                COUNT(*) as total_orders,
                SUM(CASE WHEN status_order IN ('pending', 'processing', 'completed') THEN grand_total ELSE 0 END) as revenue_today,
                SUM(CASE WHEN status_order = 'completed' THEN 1 ELSE 0 END) as completed_today
            ")
            ->first();

        $this->data['revenue_today'] = (float) ($todayStats->revenue_today ?? 0);
        $this->data['orders_today'] = (int) ($todayStats->total_orders ?? 0);
        $this->data['completed_today'] = (int) ($todayStats->completed_today ?? 0);
        $this->data['kitchen_pending'] = Order::whereIn('status_order', ['pending', 'processing'])->count();

        // Tren 7 hari terakhir dalam 1 query agregat
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        $sevenDaysData = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("
                DATE(created_at) as order_date,
                COUNT(*) as total_orders,
                SUM(CASE WHEN status_order != 'cancelled' THEN grand_total ELSE 0 END) as total_revenue
            ")
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy(function ($item) {
                return \Carbon\Carbon::parse($item->order_date)->format('Y-m-d');
            });

        $this->data['sales_chart'] = collect(range(6, 0))->map(function ($daysAgo) use ($sevenDaysData) {
            $day = now()->subDays($daysAgo);
            $key = $day->format('Y-m-d');
            $record = $sevenDaysData->get($key);

            return [
                'label' => $day->format('d M'),
                'revenue' => $record ? (float) $record->total_revenue : 0.0,
                'orders' => $record ? (int) $record->total_orders : 0,
            ];
        });

        // Transaksi terbaru
        $this->data['recent_orders'] = Order::with(['outlet', 'cashier', 'table'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', $this->data);
    }
}
