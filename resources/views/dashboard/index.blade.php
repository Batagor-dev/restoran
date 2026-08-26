@php
    // Mengambil seluruh jejak halaman (array of objects)
    $breadcrumbsData = Breadcrumbs::generate(); 
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Dashboard';
@endphp

@extends('layouts.backend.main')

@section('title', 'Dashboard')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
<div class="space-y-8 pb-12">
    <!-- Header Greeting -->
    <div>
        <h1 class="text-2xl font-satoshi-bold tracking-tight text-slate-900">Welcome Back, {{ Auth::user()->name }}!</h1>
        <p class="text-base text-slate-500 mt-1">Here is a summary of statistics, analytics, and recent activity on your system today.</p>
    </div>

    <!-- Sales KPI (outlet aktif) -->
    @php
        $salesCards = [
            [
                'label' => 'Revenue Today',
                'value' => 'Rp ' . number_format($revenue_today, 0, ',', '.'),
                'icon' => 'ri-money-dollar-circle-line',
                'note' => $orders_today . ' order(s) today',
            ],
            [
                'label' => 'Orders Today',
                'value' => number_format($orders_today),
                'icon' => 'ri-shopping-cart-2-line',
                'note' => $completed_today . ' completed',
            ],
            [
                'label' => 'In Kitchen',
                'value' => number_format($kitchen_pending),
                'icon' => 'ri-restaurant-2-line',
                'note' => 'pending & processing orders',
            ],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        @foreach($salesCards as $card)
            <x-ui.card class="p-6 flex flex-col justify-between min-h-[150px] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800">
                            <i class="{{ $card['icon'] }} text-lg"></i>
                        </div>
                        <span class="text-sm font-satoshi-bold text-slate-900">{{ $card['label'] }}</span>
                    </div>
                    <a href="{{ route('pos.index') }}" class="text-slate-300 hover:text-slate-500 transition-colors">
                        <i class="ri-arrow-right-up-line text-lg"></i>
                    </a>
                </div>
                <div class="mt-4">
                    <h3 class="text-2xl font-satoshi-bold tracking-tight text-slate-900">{{ $card['value'] }}</h3>
                    <p class="text-xs font-satoshi-medium text-slate-500 mt-1">{{ $card['note'] }}</p>
                </div>
            </x-ui.card>
        @endforeach
    </div>

    <!-- Sales Trend 7 Hari + Transaksi Terbaru -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2 p-6 flex flex-col hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h3 class="font-satoshi-bold text-slate-900 text-base">Sales Last 7 Days</h3>
                    <p class="text-xs text-slate-500">Daily revenue and order volume for the active outlet</p>
                </div>
                <a href="{{ route('orders.index') }}" class="text-xs font-satoshi-semibold text-slate-500 hover:text-slate-900 hover:underline transition-all">View Orders</a>
            </div>
            <div class="w-full">
                <x-ui.chart
                    type="area"
                    height="260"
                    :series="[
                        ['name' => 'Revenue', 'data' => $sales_chart->pluck('revenue')->values()->all()],
                        ['name' => 'Orders', 'data' => $sales_chart->pluck('orders')->values()->all()],
                    ]"
                    :labels="$sales_chart->pluck('label')->values()->all()"
                    :colors="['#0f172a', '#94a3b8']"
                />
            </div>
        </x-ui.card>

        <x-ui.card class="p-6 flex flex-col hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <h3 class="font-satoshi-bold text-slate-900 text-base">Recent Orders</h3>
                <a href="{{ route('orders.index') }}" class="text-xs font-satoshi-semibold text-slate-500 hover:text-slate-900 hover:underline transition-all">View All</a>
            </div>
            <div class="divide-y divide-slate-100/70 flex-1 overflow-y-auto max-h-[320px]">
                @forelse($recent_orders as $order)
                    <a href="{{ route('orders.show', $order->uuid) }}" class="flex items-center justify-between py-3 first:pt-0 last:pb-0 group">
                        <div class="min-w-0">
                            <p class="text-sm font-satoshi-bold text-slate-900 truncate group-hover:underline">#{{ $order->code_invoice }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ $order->customer_name ?? 'Guest' }} · {{ $order->created_at->format('d M H:i') }}</p>
                        </div>
                        <div class="text-right ml-3 flex-shrink-0">
                            <p class="text-sm font-satoshi-bold text-slate-900">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-satoshi-semibold
                                {{ $order->status_order === 'completed' ? 'bg-emerald-50 text-emerald-600' : '' }}
                                {{ $order->status_order === 'pending' ? 'bg-slate-100 text-slate-600' : '' }}
                                {{ $order->status_order === 'processing' ? 'bg-slate-900 text-white' : '' }}
                                {{ $order->status_order === 'cancelled' ? 'bg-rose-50 text-rose-600' : '' }}">
                                {{ ucfirst($order->status_order) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="py-8 text-center text-sm text-slate-400 font-satoshi-regular">No orders yet today.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Stat Card 1: Total Artikel -->
        <x-ui.card class="p-6 flex flex-col justify-between min-h-[170px] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800">
                        <i class="ri-article-line text-lg"></i>
                    </div>
                    <span class="text-sm font-satoshi-bold text-slate-900">Total Articles</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-satoshi-bold tracking-tight text-slate-900">{{ $total_articles }}</h3>
                    <span class="inline-flex items-center gap-0.5 text-xs font-satoshi-semibold text-emerald-600">
                        <i class="ri-arrow-up-line"></i> 12%
                    </span>
                </div>
                <p class="text-xs font-satoshi-medium text-slate-500 mt-1">Active articles published this week</p>
            </div>
        </x-ui.card>

        <!-- Stat Card 2: Kategori -->
        <x-ui.card class="p-6 flex flex-col justify-between min-h-[170px] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800">
                        <i class="ri-folder-line text-lg"></i>
                    </div>
                    <span class="text-sm font-satoshi-bold text-slate-900">Categories</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-satoshi-bold tracking-tight text-slate-900">{{ $total_categories }}</h3>
                    <span class="inline-flex items-center gap-0.5 text-xs font-satoshi-semibold text-slate-500">
                        Stable
                    </span>
                </div>
                <p class="text-xs font-satoshi-medium text-slate-500 mt-1">System category groups</p>
            </div>
        </x-ui.card>

        <!-- Stat Card 3: Total Pengguna -->
        <x-ui.card class="p-6 flex flex-col justify-between min-h-[170px] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800">
                        <i class="ri-user-line text-lg"></i>
                    </div>
                    <span class="text-sm font-satoshi-bold text-slate-900">Total Users</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-satoshi-bold tracking-tight text-slate-900">{{ $total_users }}</h3>
                    <span class="inline-flex items-center gap-0.5 text-xs font-satoshi-semibold text-emerald-600">
                        <i class="ri-arrow-up-line"></i> +4
                    </span>
                </div>
                <p class="text-xs font-satoshi-medium text-slate-500 mt-1">Accounts registered today</p>
            </div>
        </x-ui.card>

        <!-- Stat Card 4: Kunjungan -->
        <x-ui.card class="p-6 flex flex-col justify-between min-h-[170px] hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-950 text-white border border-slate-800">
                        <i class="ri-eye-line text-lg"></i>
                    </div>
                    <span class="text-sm font-satoshi-bold text-slate-900">Visits</span>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-satoshi-bold tracking-tight text-slate-900">3.8K</h3>
                    <span class="inline-flex items-center gap-0.5 text-xs font-satoshi-semibold text-rose-600">
                        <i class="ri-arrow-down-line"></i> -2%
                    </span>
                </div>
                <p class="text-xs font-satoshi-medium text-slate-500 mt-1">Unique visitors yesterday</p>
            </div>
        </x-ui.card>
    </div>

    <!-- Analytics Charts (Premium Interactive Graphs) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Overview Chart Card (Interactive Bar Chart) -->
        <x-ui.card class="lg:col-span-2 p-6 flex flex-col justify-between hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h3 class="font-satoshi-bold text-slate-900 text-base">Visitor Analytics</h3>
                    <p class="text-xs text-slate-500">Visit performance and interaction statistics for the past 12 months</p>
                </div>
                <div class="flex items-center gap-1.5 text-xs font-satoshi-semibold text-slate-500">
                    <span>Filter:</span>
                    <button class="flex items-center gap-0.5 text-slate-900 font-satoshi-bold hover:underline">
                        This Year <i class="ri-arrow-down-s-line"></i>
                    </button>
                </div>
            </div>

            <!-- Apex Chart Area -->
            <div class="w-full">
                <x-ui.chart 
                    type="bar"
                    height="280"
                    :series="[
                        ['name' => 'Unique Visitors', 'data' => [1200, 1900, 3200, 5400, 4800, 6000, 7300, 8600, 9200, 8000, 9000, 9600]],
                        ['name' => 'Page Interactions', 'data' => [800, 1400, 2300, 4100, 3500, 4500, 5800, 6900, 7500, 6200, 7100, 7900]]
                    ]"
                    :labels="['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']"
                    :colors="['#0f172a', '#6366f1']"
                />
            </div>
        </x-ui.card>

        <!-- Server Capacity Card (Radial Bar Chart) -->
        <x-ui.card class="p-6 flex flex-col justify-between hover:shadow-xl transition-all duration-300">
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h3 class="font-satoshi-bold text-slate-900 text-base">Server Capacity</h3>
                <p class="text-xs text-slate-500">Real-time hosting resource load</p>
            </div>
            
            <div class="flex-1 flex items-center justify-center py-2">
                <x-ui.chart 
                    type="radialBar"
                    height="250"
                    :series="[42, 18, 76]"
                    :labels="['SSD Space', 'Database', 'RAM Cache']"
                    :colors="['#0f172a', '#10b981', '#6366f1']"
                />
            </div>

            <div class="mt-4 pt-3 text-[11px] text-slate-500 border-t border-slate-50 flex items-center gap-1.5">
                <i class="ri-refresh-line animate-spin text-slate-300"></i> Auto-synced 1 minute ago.
            </div>
        </x-ui.card>
    </div>

    <!-- Recent Articles Section -->
    <div class="grid grid-cols-1 gap-6">
        <!-- Recent Articles Card -->
        <x-ui.card class="p-6 flex flex-col hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <h3 class="font-satoshi-bold text-slate-900 text-base">Recent Articles</h3>
                <a href="{{ route('article.index') }}" class="text-xs font-satoshi-semibold text-slate-500 hover:text-slate-900 hover:underline transition-all">View All</a>
            </div>
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead>
                        <tr class="text-slate-900 font-satoshi-semibold border-b border-slate-100 text-xs uppercase">
                            <th class="py-3 pr-4">Article Title</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Author</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 pl-4 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 font-satoshi-medium text-slate-700">
                        @forelse($recent_articles as $article)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 pr-4 text-slate-900 font-satoshi-bold">{{ $article->title }}</td>
                                <td class="py-3.5 px-4">
                                    <x-ui.badge variant="neutral">
                                        {{ $article->category->name ?? 'Uncategorized' }}
                                    </x-ui.badge>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">{{ $article->author->name ?? 'System' }}</td>
                                <td class="py-3.5 px-4">
                                    <x-ui.badge variant="success" icon="ri-checkbox-circle-line">
                                        Published
                                    </x-ui.badge>
                                </td>
                                <td class="py-3.5 pl-4 text-right text-slate-500">
                                    {{ \Carbon\Carbon::parse($article->published_at)->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 font-satoshi-regular">
                                    No articles available in the database yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    <!-- User Contributors & Activity Logs -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Top Authors Widget -->
        <x-ui.card class="p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <div>
                    <h3 class="font-satoshi-bold text-slate-900 text-base">Top Authors</h3>
                    <p class="text-xs text-slate-500">Article contributors this month</p>
                </div>
                <button class="text-slate-300 hover:text-slate-500 transition-colors">
                    <i class="ri-more-fill text-lg"></i>
                </button>
            </div>
            
            <!-- List Author -->
            <div class="divide-y divide-slate-100/60">
                <!-- User 1 -->
                <div class="flex items-center justify-between py-3.5 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-3">
                        <div class="relative p-0.5 rounded-full border border-slate-100">
                            <img src="{{ asset('assets/img/avatar/avatar-2.jpg') }}" alt="Putri" class="h-10 w-10 rounded-full object-cover">
                        </div>
                        <div>
                            <h4 class="text-sm font-satoshi-bold text-slate-900 leading-tight">Putri Handayani</h4>
                            <p class="text-[11px] text-slate-500">putri@gmail.com</p>
                        </div>
                    </div>
                    <x-ui.badge variant="primary" icon="ri-bookmark-3-line">
                        42 Posts
                    </x-ui.badge>
                </div>
                <!-- User 2 -->
                <div class="flex items-center justify-between py-3.5 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-3">
                        <div class="relative p-0.5 rounded-full border border-slate-100">
                            <img src="{{ asset('assets/img/avatar/avatar-1.jpg') }}" alt="Andi" class="h-10 w-10 rounded-full object-cover">
                        </div>
                        <div>
                            <h4 class="text-sm font-satoshi-bold text-slate-900 leading-tight">Andi Wijaya</h4>
                            <p class="text-[11px] text-slate-500">andi@gmail.com</p>
                        </div>
                    </div>
                    <x-ui.badge variant="primary" icon="ri-bookmark-3-line">
                        28 Posts
                    </x-ui.badge>
                </div>
                <!-- User 3 -->
                <div class="flex items-center justify-between py-3.5 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-3">
                        <div class="relative p-0.5 rounded-full border border-slate-100">
                            <img src="{{ asset('assets/img/avatar/avatar-3.jpg') }}" alt="Budi" class="h-10 w-10 rounded-full object-cover">
                        </div>
                        <div>
                            <h4 class="text-sm font-satoshi-bold text-slate-900 leading-tight">Budi Santoso</h4>
                            <p class="text-[11px] text-slate-500">budi@gmail.com</p>
                        </div>
                    </div>
                    <x-ui.badge variant="primary" icon="ri-bookmark-3-line">
                        14 Posts
                    </x-ui.badge>
                </div>
            </div>
        </x-ui.card>

        <!-- System Logs Card -->
        <x-ui.card class="lg:col-span-2 p-6 hover:shadow-xl transition-all duration-300">
            <div class="border-b border-slate-100 pb-4 mb-4">
                <h3 class="font-satoshi-bold text-slate-900 text-base">System Activity Logs</h3>
                <p class="text-xs text-slate-500">History of crucial application operations</p>
            </div>
            <div class="space-y-4">
                <!-- Log 1 -->
                <div class="flex items-start gap-3 text-base py-1 hover:bg-slate-50/20 px-2 rounded-xl transition-all duration-200">
                    <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100/30">
                        <i class="ri-check-line text-base"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-satoshi-semibold text-slate-900 text-sm">Automated database backup completed successfully.</p>
                        <span class="text-xs text-slate-500">Today, 04:00 AM</span>
                    </div>
                </div>
                <!-- Log 2 -->
                <div class="flex items-start gap-3 text-base py-1 hover:bg-slate-50/20 px-2 rounded-xl transition-all duration-200">
                    <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 border border-blue-100/30">
                        <i class="ri-user-add-line text-base"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-satoshi-semibold text-slate-900 text-sm">New article metadata synchronization completed.</p>
                        <span class="text-xs text-slate-500">Yesterday, 08:14 PM</span>
                    </div>
                </div>
                <!-- Log 3 -->
                <div class="flex items-start gap-3 text-base py-1 hover:bg-slate-50/20 px-2 rounded-xl transition-all duration-200">
                    <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 border border-amber-100/30">
                        <i class="ri-shield-flash-line text-base"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-satoshi-semibold text-slate-900 text-sm">Failed login attempt detected from IP 192.168.1.105 (3 failed attempts).</p>
                        <span class="text-xs text-slate-500">11 Jul 2026, 11:22 AM</span>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</div>
@endsection