<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Registry seluruh laporan (dipakai hub, breadcrumb, dan validasi).
     */
    public static function registry(): array
    {
        return [
            'sales' => ['label' => 'Sales Report', 'icon' => 'ri-line-chart-line', 'desc' => 'Ringkasan penjualan harian dalam periode', 'dates' => true],
            'transactions' => ['label' => 'Transaction Report', 'icon' => 'ri-receipt-line', 'desc' => 'Daftar seluruh transaksi dalam periode', 'dates' => true],
            'product-sales' => ['label' => 'Product Sales Report', 'icon' => 'ri-shopping-bag-3-line', 'desc' => 'Penjualan per produk', 'dates' => true],
            'category-sales' => ['label' => 'Category Sales Report', 'icon' => 'ri-folder-chart-line', 'desc' => 'Penjualan per kategori produk', 'dates' => true],
            'cashier' => ['label' => 'Cashier Report', 'icon' => 'ri-user-star-line', 'desc' => 'Performa penjualan per kasir', 'dates' => true],
            'payment-method' => ['label' => 'Payment Method Report', 'icon' => 'ri-bank-card-line', 'desc' => 'Distribusi transaksi per metode pembayaran', 'dates' => true],
            'tax' => ['label' => 'Tax Report', 'icon' => 'ri-percent-line', 'desc' => 'Pajak terkumpul per hari', 'dates' => true],
            'discount' => ['label' => 'Discount Report', 'icon' => 'ri-coupon-line', 'desc' => 'Diskon / voucher yang diberikan', 'dates' => true],
            'stock' => ['label' => 'Stock Report', 'icon' => 'ri-archive-line', 'desc' => 'Posisi stok terkini per produk', 'dates' => false],
            'stock-movement' => ['label' => 'Stock Movement Report', 'icon' => 'ri-arrow-left-right-line', 'desc' => 'Riwayat pergerakan stok', 'dates' => true],
            'low-stock' => ['label' => 'Low Stock Report', 'icon' => 'ri-alarm-warning-line', 'desc' => 'Produk dengan stok di bawah ambang batas', 'dates' => false],
            'refund' => ['label' => 'Refund Report', 'icon' => 'ri-arrow-go-back-line', 'desc' => 'Transaksi yang di-refund', 'dates' => true],
            'void' => ['label' => 'Void Transaction Report', 'icon' => 'ri-close-circle-line', 'desc' => 'Transaksi yang dibatalkan', 'dates' => true],
            'profit' => ['label' => 'Profit Report', 'icon' => 'ri-money-dollar-circle-line', 'desc' => 'Margin keuntungan berdasarkan harga modal (HPP)', 'dates' => true],
            'daily-closing' => ['label' => 'Daily Closing Report', 'icon' => 'ri-calendar-check-line', 'desc' => 'Tutup harian: total & rincian metode pembayaran', 'dates' => true],
        ];
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()->can('Report Access'), 403);
    }

    /**
     * Hub daftar laporan.
     */
    public function index()
    {
        $this->authorizeAccess();

        return view('reports.index', [
            'registry' => static::registry(),
        ]);
    }

    /**
     * Tampilkan satu laporan.
     */
    public function show(Request $request, string $type)
    {
        $this->authorizeAccess();

        $registry = static::registry();
        abort_unless(isset($registry[$type]), 404);

        [$start, $end] = $this->resolveRange($request);
        $data = $this->build($type, $start, $end);

        return view('reports.show', [
            'type' => $type,
            'meta' => $registry[$type],
            'startDate' => $start,
            'endDate' => $end,
            'threshold' => (int) $request->query('threshold', 5),
            'summary' => $data['summary'],
            'columns' => $data['columns'],
            'rows' => $data['rows'],
        ]);
    }

    /**
     * Export CSV laporan.
     */
    public function export(Request $request, string $type)
    {
        $this->authorizeAccess();

        $registry = static::registry();
        abort_unless(isset($registry[$type]), 404);

        [$start, $end] = $this->resolveRange($request);
        $data = $this->build($type, $start, $end);
        $label = str_replace(' ', '_', $registry[$type]['label']);
        $filename = $label.'_'.now()->format('YmdHis').'.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, array_column($data['columns'], 'label'));

            foreach ($data['rows'] as $row) {
                fputcsv($out, array_map(
                    fn ($col) => $row[$col['key']] ?? '',
                    $data['columns']
                ));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function resolveRange(Request $request): array
    {
        $start = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $end = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        return [$start, $end];
    }

    /**
     * Bangun data laporan generik: columns + rows (+ optional summary cards).
     * Semua query otomatis ter-scope outlet aktif lewat global scope model.
     */
    private function build(string $type, Carbon $start, Carbon $end): array
    {
        return match ($type) {
            'sales' => $this->salesReport($start, $end),
            'transactions' => $this->transactionsReport($start, $end),
            'product-sales' => $this->productSalesReport($start, $end),
            'category-sales' => $this->categorySalesReport($start, $end),
            'cashier' => $this->cashierReport($start, $end),
            'payment-method' => $this->paymentMethodReport($start, $end),
            'tax' => $this->taxReport($start, $end),
            'discount' => $this->discountReport($start, $end),
            'stock' => $this->stockReport(),
            'stock-movement' => $this->stockMovementReport($start, $end),
            'low-stock' => $this->lowStockReport(request()->query('threshold', 5)),
            'refund' => $this->refundReport($start, $end),
            'void' => $this->voidReport($start, $end),
            'profit' => $this->profitReport($start, $end),
            'daily-closing' => $this->dailyClosingReport($start, $end),
            default => abort(404),
        };
    }

    // ---------------------------------------------------------------
    // Base queries
    // ---------------------------------------------------------------

    /**
     * Order valid (status normal) dalam rentang tanggal.
     */
    private function validOrders(Carbon $start, Carbon $end)
    {
        // Prefix "orders." penting agar tidak ambigu ketika builder
        // melakukan join ke tabel lain (mis. users).
        return Order::valid()->whereBetween('orders.created_at', [$start, $end]);
    }

    /**
     * Item dari order valid dalam rentang tanggal.
     */
    private function soldItems(Carbon $start, Carbon $end)
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status_transaction', 'normal')
            ->whereBetween('orders.created_at', [$start, $end]);
    }

    // ---------------------------------------------------------------
    // Builders
    // ---------------------------------------------------------------

    private function salesReport(Carbon $start, Carbon $end): array
    {
        $q = $this->validOrders($start, $end);

        $summary = [
            ['label' => 'Gross Sales', 'value' => 'Rp '.number_format((float) (clone $q)->sum('subtotal'), 0, ',', '.')],
            ['label' => 'Discount', 'value' => '-Rp '.number_format((float) (clone $q)->sum('discount'), 0, ',', '.')],
            ['label' => 'Tax Collected', 'value' => 'Rp '.number_format((float) (clone $q)->sum('tax'), 0, ',', '.')],
            ['label' => 'Net Revenue', 'value' => 'Rp '.number_format((float) (clone $q)->sum('grand_total'), 0, ',', '.'), 'strong' => true],
        ];

        $rows = (clone $q)
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM-DD') as col_date")
            ->selectRaw('COUNT(*) as col_trx')
            ->selectRaw('SUM(subtotal) as col_gross')
            ->selectRaw('SUM(discount) as col_disc')
            ->selectRaw('SUM(tax) as col_tax')
            ->selectRaw('SUM(grand_total) as col_net')
            ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD')"))
            ->orderBy('col_date', 'desc')
            ->get()
            ->map(fn ($r) => [
                'Date' => Carbon::parse($r->col_date)->format('d M Y'),
                'Transactions' => $r->col_trx,
                'Gross Sales' => 'Rp '.number_format((float) $r->col_gross, 0, ',', '.'),
                'Discount' => '-Rp '.number_format((float) $r->col_disc, 0, ',', '.'),
                'Tax' => 'Rp '.number_format((float) $r->col_tax, 0, ',', '.'),
                'Net Revenue' => 'Rp '.number_format((float) $r->col_net, 0, ',', '.'),
            ])->all();

        return [
            'summary' => $summary,
            'columns' => $this->cols(['Date', 'Transactions', 'Gross Sales', 'Discount', 'Tax', 'Net Revenue'], [1]),
            'rows' => $rows,
        ];
    }

    private function transactionsReport(Carbon $start, Carbon $end): array
    {
        $rows = $this->validOrders($start, $end)
            ->with(['cashier:id,name', 'table:id,number_table'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($o) => [
                'Invoice' => '#'.$o->code_invoice,
                'Date' => $o->created_at->format('d M Y H:i'),
                'Cashier' => $o->cashier?->name ?? '-',
                'Customer' => $o->customer_name ?? '-',
                'Type' => $o->order_type === 'takeaway' ? 'Take Away' : 'Dine In',
                'Table' => $o->table?->number_table ?? '-',
                'Payment' => strtoupper($o->payment_method),
                'Total' => 'Rp '.number_format((float) $o->grand_total, 0, ',', '.'),
            ])->all();

        return [
            'summary' => [],
            'columns' => $this->cols(['Invoice', 'Date', 'Cashier', 'Customer', 'Type', 'Table', 'Payment', 'Total']),
            'rows' => $rows,
        ];
    }

    private function productSalesReport(Carbon $start, Carbon $end): array
    {
        $rows = $this->soldItems($start, $end)
            ->selectRaw('order_items.product_id, order_items.product_name')
            ->selectRaw('SUM(order_items.quantity) as col_qty')
            ->selectRaw('SUM(order_items.subtotal) as col_net')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) as col_orders')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('col_net')
            ->get()
            ->map(fn ($r) => [
                'Product' => $r->product_name,
                'Qty Sold' => $r->col_qty,
                'Orders' => $r->col_orders,
                'Sales' => 'Rp '.number_format((float) $r->col_net, 0, ',', '.'),
            ])->all();

        return ['summary' => [], 'columns' => $this->cols(['Product', 'Qty Sold', 'Orders', 'Sales']), 'rows' => $rows];
    }

    private function categorySalesReport(Carbon $start, Carbon $end): array
    {
        $rows = $this->soldItems($start, $end)
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->selectRaw("COALESCE(product_categories.name, 'Uncategorized') as cat_name")
            ->selectRaw('SUM(order_items.quantity) as col_qty')
            ->selectRaw('SUM(order_items.subtotal) as col_net')
            ->groupBy('cat_name')
            ->orderByDesc('col_net')
            ->get()
            ->map(fn ($r) => [
                'Category' => $r->cat_name,
                'Qty Sold' => $r->col_qty,
                'Sales' => 'Rp '.number_format((float) $r->col_net, 0, ',', '.'),
            ])->all();

        return ['summary' => [], 'columns' => $this->cols(['Category', 'Qty Sold', 'Sales']), 'rows' => $rows];
    }

    private function cashierReport(Carbon $start, Carbon $end): array
    {
        $rows = $this->validOrders($start, $end)
            ->join('users', 'users.id', '=', 'orders.cashier_id')
            ->selectRaw('users.name as cashier_name')
            ->selectRaw('COUNT(*) as col_trx')
            ->selectRaw('SUM(grand_total) as col_net')
            ->selectRaw('ROUND(AVG(grand_total)) as col_avg')
            ->groupBy('users.name')
            ->orderByDesc('col_net')
            ->get()
            ->map(fn ($r) => [
                'Cashier' => $r->cashier_name,
                'Transactions' => $r->col_trx,
                'Total Sales' => 'Rp '.number_format((float) $r->col_net, 0, ',', '.'),
                'Avg Ticket' => 'Rp '.number_format((float) $r->col_avg, 0, ',', '.'),
            ])->all();

        return ['summary' => [], 'columns' => $this->cols(['Cashier', 'Transactions', 'Total Sales', 'Avg Ticket']), 'rows' => $rows];
    }

    private function paymentMethodReport(Carbon $start, Carbon $end): array
    {
        $rows = $this->validOrders($start, $end)
            ->selectRaw('payment_method')
            ->selectRaw('COUNT(*) as col_trx')
            ->selectRaw('SUM(grand_total) as col_net')
            ->groupBy('payment_method')
            ->orderByDesc('col_net')
            ->get();

        $totalTrx = max(1, $rows->sum('col_trx'));

        return [
            'summary' => [],
            'columns' => $this->cols(['Payment Method', 'Transactions', 'Share', 'Total'], [2]),
            'rows' => $rows->map(fn ($r) => [
                'Payment Method' => strtoupper($r->payment_method),
                'Transactions' => $r->col_trx,
                'Share' => round(($r->col_trx / $totalTrx) * 100).'%',
                'Total' => 'Rp '.number_format((float) $r->col_net, 0, ',', '.'),
            ])->all(),
        ];
    }

    private function taxReport(Carbon $start, Carbon $end): array
    {
        $q = $this->validOrders($start, $end);

        $summary = [
            ['label' => 'Total Tax Collected', 'value' => 'Rp '.number_format((float) (clone $q)->sum('tax'), 0, ',', '.'), 'strong' => true],
            ['label' => 'Taxed Transactions', 'value' => number_format((clone $q)->where('tax', '>', 0)->count())],
        ];

        $rows = (clone $q)
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM-DD') as col_date")
            ->selectRaw('COUNT(*) as col_trx')
            ->selectRaw('SUM(tax) as col_tax')
            ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD')"))
            ->orderBy('col_date', 'desc')
            ->get()
            ->map(fn ($r) => [
                'Date' => Carbon::parse($r->col_date)->format('d M Y'),
                'Transactions' => $r->col_trx,
                'Tax Collected' => 'Rp '.number_format((float) $r->col_tax, 0, ',', '.'),
            ])->all();

        return ['summary' => $summary, 'columns' => $this->cols(['Date', 'Transactions', 'Tax Collected']), 'rows' => $rows];
    }

    private function discountReport(Carbon $start, Carbon $end): array
    {
        $base = Order::whereBetween('created_at', [$start, $end])
            ->where('discount', '>', 0);

        $summary = [
            ['label' => 'Total Discount Given', 'value' => 'Rp '.number_format((float) (clone $base)->sum('discount'), 0, ',', '.'), 'strong' => true],
            ['label' => 'Discounted Transactions', 'value' => number_format((clone $base)->count())],
        ];

        $rows = $base->with('promo:id,name')->orderByDesc('discount')
            ->get()
            ->map(fn ($o) => [
                'Invoice' => '#'.$o->code_invoice,
                'Date' => $o->created_at->format('d M Y H:i'),
                'Voucher' => $o->promo?->name ?? '-',
                'Customer' => $o->customer_name ?? '-',
                'Order Total' => 'Rp '.number_format((float) $o->grand_total, 0, ',', '.'),
                'Discount' => '-Rp '.number_format((float) $o->discount, 0, ',', '.'),
            ])->all();

        return [
            'summary' => $summary,
            'columns' => $this->cols(['Invoice', 'Date', 'Voucher', 'Customer', 'Order Total', 'Discount']),
            'rows' => $rows,
        ];
    }

    private function stockReport(): array
    {
        $rows = ProductStock::with(['product:id,name,price'])
            ->orderBy('quantity')
            ->get()
            ->map(fn ($s) => [
                'Product' => $s->product?->name ?? '-',
                'Quantity' => $s->quantity,
                'Outlet Price' => $s->price !== null ? 'Rp '.number_format((float) $s->price, 0, ',', '.') : '-',
                'Base Price' => 'Rp '.number_format((float) ($s->product?->price ?? 0), 0, ',', '.'),
                'Stock Value' => 'Rp '.number_format($s->quantity * (float) ($s->price ?? $s->product?->price ?? 0), 0, ',', '.'),
            ])->all();

        return ['summary' => [], 'columns' => $this->cols(['Product', 'Quantity', 'Outlet Price', 'Base Price', 'Stock Value']), 'rows' => $rows];
    }

    private function stockMovementReport(Carbon $start, Carbon $end): array
    {
        $rows = StockMovement::with(['productStock.product:id,name', 'createdBy:id,name'])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->limit(1000)
            ->get()
            ->map(fn ($m) => [
                'Date' => $m->created_at->format('d M Y H:i'),
                'Product' => $m->productStock?->product?->name ?? '-',
                'Type' => ucfirst($m->movement_type),
                'Qty' => $m->quantity,
                'Before' => $m->stock_before,
                'After' => $m->stock_after,
                'Notes' => $m->notes ?? '-',
                'By' => $m->createdBy?->name ?? '-',
            ])->all();

        return [
            'summary' => [],
            'columns' => $this->cols(['Date', 'Product', 'Type', 'Qty', 'Before', 'After', 'Notes', 'By']),
            'rows' => $rows,
        ];
    }

    private function lowStockReport(mixed $threshold): array
    {
        $threshold = max(0, min(100000, (int) $threshold));

        $rows = ProductStock::with('product:id,name,price')
            ->where('quantity', '<=', $threshold)
            ->orderBy('quantity')
            ->get()
            ->map(fn ($s) => [
                'Product' => $s->product?->name ?? '-',
                'Quantity' => $s->quantity,
                'Threshold' => $threshold,
            ])->all();

        return [
            'summary' => [
                ['label' => 'Products at/below threshold', 'value' => number_format(count($rows)), 'strong' => true],
                ['label' => 'Threshold', 'value' => $threshold],
            ],
            'columns' => $this->cols(['Product', 'Quantity', 'Threshold']),
            'rows' => $rows,
        ];
    }

    private function refundReport(Carbon $start, Carbon $end): array
    {
        $rows = Order::where('status_transaction', 'refunded')
            ->whereBetween('refunded_at', [$start, $end])
            ->with(['refundedBy:id,name'])
            ->orderByDesc('refunded_at')
            ->get();

        $summary = [
            ['label' => 'Refunded Amount', 'value' => 'Rp '.number_format((float) $rows->sum('grand_total'), 0, ',', '.'), 'strong' => true],
            ['label' => 'Refund Count', 'value' => number_format($rows->count())],
        ];

        return [
            'summary' => $summary,
            'columns' => $this->cols(['Invoice', 'Order Date', 'Refunded At', 'Refund Reason', 'Amount']),
            'rows' => $rows->map(fn ($o) => [
                'Invoice' => '#'.$o->code_invoice,
                'Order Date' => $o->created_at->format('d M Y H:i'),
                'Refunded At' => optional($o->refunded_at)->format('d M Y H:i'),
                'Refund Reason' => $o->refund_reason ?? '-',
                'Amount' => 'Rp '.number_format((float) $o->grand_total, 0, ',', '.'),
            ])->all(),
        ];
    }

    private function voidReport(Carbon $start, Carbon $end): array
    {
        $rows = Order::where('status_transaction', 'voided')
            ->whereBetween('voided_at', [$start, $end])
            ->with(['voidedBy:id,name'])
            ->orderByDesc('voided_at')
            ->get();

        $summary = [
            ['label' => 'Voided Amount', 'value' => 'Rp '.number_format((float) $rows->sum('grand_total'), 0, ',', '.'), 'strong' => true],
            ['label' => 'Void Count', 'value' => number_format($rows->count())],
        ];

        return [
            'summary' => $summary,
            'columns' => $this->cols(['Invoice', 'Order Date', 'Voided At', 'Void Reason', 'Amount']),
            'rows' => $rows->map(fn ($o) => [
                'Invoice' => '#'.$o->code_invoice,
                'Order Date' => $o->created_at->format('d M Y H:i'),
                'Voided At' => optional($o->voided_at)->format('d M Y H:i'),
                'Void Reason' => $o->void_reason ?? '-',
                'Amount' => 'Rp '.number_format((float) $o->grand_total, 0, ',', '.'),
            ])->all(),
        ];
    }

    private function profitReport(Carbon $start, Carbon $end): array
    {
        $rows = $this->soldItems($start, $end)
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('order_items.product_name')
            ->selectRaw('SUM(order_items.quantity) as col_qty')
            ->selectRaw('SUM(order_items.subtotal) as col_revenue')
            ->selectRaw('SUM(CASE WHEN products.cost_price IS NULL THEN 0 ELSE products.cost_price * order_items.quantity END) as col_cost')
            ->selectRaw('BOOL_OR(products.cost_price IS NULL) as col_missing_cost')
            ->groupBy('order_items.product_name')
            ->orderByDesc('col_revenue')
            ->get();

        $totalRevenue = (float) $rows->sum('col_revenue');
        $totalCost = (float) $rows->sum('col_cost');

        $missingCount = $rows->where('col_missing_cost', true)->count();

        return [
            'summary' => [
                ['label' => 'Revenue', 'value' => 'Rp '.number_format($totalRevenue, 0, ',', '.')],
                ['label' => 'Est. Cost (HPP)', 'value' => 'Rp '.number_format($totalCost, 0, ',', '.')],
                ['label' => 'Gross Profit', 'value' => 'Rp '.number_format($totalRevenue - $totalCost, 0, ',', '.'), 'strong' => true],
            ],
            'columns' => $this->cols(['Product', 'Qty Sold', 'Revenue', 'Est. Cost', 'Profit']),
            'rows' => $rows->map(fn ($r) => [
                'Product' => $r->product_name.($r->col_missing_cost ? ' *' : ''),
                'Qty Sold' => $r->col_qty,
                'Revenue' => 'Rp '.number_format((float) $r->col_revenue, 0, ',', '.'),
                'Est. Cost' => 'Rp '.number_format((float) $r->col_cost, 0, ',', '.'),
                'Profit' => 'Rp '.number_format((float) $r->col_revenue - (float) $r->col_cost, 0, ',', '.'),
            ])->all(),
        ];
    }

    private function dailyClosingReport(Carbon $start, Carbon $end): array
    {
        $rows = $this->validOrders($start, $end)
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM-DD') as col_date")
            ->selectRaw('COUNT(*) as col_trx')
            ->selectRaw("SUM(CASE WHEN payment_method = 'cash' THEN grand_total ELSE 0 END) as col_cash")
            ->selectRaw("SUM(CASE WHEN payment_method = 'qris' THEN grand_total ELSE 0 END) as col_qris")
            ->selectRaw("SUM(CASE WHEN payment_method = 'debit' THEN grand_total ELSE 0 END) as col_debit")
            ->selectRaw("SUM(CASE WHEN payment_method = 'credit' THEN grand_total ELSE 0 END) as col_credit")
            ->selectRaw('SUM(grand_total) as col_total')
            ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD')"))
            ->orderBy('col_date', 'desc')
            ->get()
            ->map(fn ($r) => [
                'Date' => Carbon::parse($r->col_date)->format('d M Y'),
                'Transactions' => $r->col_trx,
                'Cash' => 'Rp '.number_format((float) $r->col_cash, 0, ',', '.'),
                'QRIS' => 'Rp '.number_format((float) $r->col_qris, 0, ',', '.'),
                'Debit' => 'Rp '.number_format((float) $r->col_debit, 0, ',', '.'),
                'Credit' => 'Rp '.number_format((float) $r->col_credit, 0, ',', '.'),
                'Closing Total' => 'Rp '.number_format((float) $r->col_total, 0, ',', '.'),
            ])->all();

        return [
            'summary' => [],
            'columns' => $this->cols(['Date', 'Transactions', 'Cash', 'QRIS', 'Debit', 'Credit', 'Closing Total']),
            'rows' => $rows,
        ];
    }

    /**
     * Definisi kolom generik. Index angka = kolom yang dirata kanan (angka).
     */
    private function cols(array $labels, array $rightAlign = []): array
    {
        return array_map(function ($label) use ($rightAlign, $labels) {
            $index = array_search($label, $labels);

            return [
                'label' => $label,
                'key' => $label,
                'align' => in_array($index, $rightAlign, true) ? 'right' : 'left',
            ];
        }, $labels);
    }
}
