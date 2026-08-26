<?php

namespace App\Http\Controllers;

use App\DataTables\TransactionDataTable;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactions) {}

    /**
     * Riwayat transaksi POS (outlet aktif).
     */
    public function index(TransactionDataTable $dataTable)
    {
        abort_unless(auth()->user()->can('Transaction Access'), 403);

        $cashiers = User::whereHas('orders')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Outlet selector hanya untuk Super Admin/Owner (karyawan terkunci global scope)
        $outlets = auth()->user()->hasRole(['Super Admin', 'Owner'])
            ? Outlet::where('status', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return $dataTable->render('transactions.index', [
            'cashiers' => $cashiers,
            'outlets' => $outlets,
        ]);
    }

    /**
     * Export riwayat transaksi.
     * format=csv -> unduh CSV (kompatibel Excel)
     * format=pdf -> halaman print-friendly (simpan sebagai PDF via print dialog)
     */
    public function export(Request $request)
    {
        abort_unless(auth()->user()->can('Transaction Access'), 403);

        $query = Order::with(['cashier', 'table'])->select('orders.*');
        $this->applyFilters($query);

        if ($request->get('format') === 'csv') {
            $filename = 'Transactions_'.date('YmdHis').'.csv';

            return response()->streamDownload(function () use ($query) {
                $out = fopen('php://output', 'w');

                // BOM agar Excel membaca UTF-8 dengan benar
                fwrite($out, "\xEF\xBB\xBF");

                fputcsv($out, [
                    'Invoice', 'Date', 'Cashier', 'Customer', 'Type',
                    'Payment', 'Subtotal', 'Discount', 'Tax', 'Grand Total', 'Status',
                ]);

                // orderBy id (unik) agar chunking deterministik di PostgreSQL
                $query->orderBy('orders.id')->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $row) {
                        fputcsv($out, [
                            $row->code_invoice,
                            $row->created_at->format('d/m/Y H:i'),
                            $row->cashier?->name ?? '-',
                            $row->customer_name ?? '-',
                            $row->order_type === 'takeaway' ? 'Take Away' : 'Dine In',
                            strtoupper($row->payment_method),
                            number_format((float) $row->subtotal, 2, ',', ''),
                            number_format((float) $row->discount, 2, ',', ''),
                            number_format((float) $row->tax, 2, ',', ''),
                            number_format((float) $row->grand_total, 2, ',', ''),
                            ucfirst($row->status_transaction === 'normal' ? 'success' : $row->status_transaction),
                        ]);
                    }
                });

                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        // format=pdf (print view)
        $transactions = $query->orderBy('orders.created_at')->limit(1000)->get();

        return view('transactions.export-pdf', [
            'transactions' => $transactions,
            'generatedAt' => now(),
        ]);
    }

    /**
     * Terapkan filter standar riwayat transaksi pada query.
     */
    private function applyFilters($query): void
    {
        if (request()->filled('start_date')) {
            $query->whereDate('orders.created_at', '>=', request('start_date'));
        }
        if (request()->filled('end_date')) {
            $query->whereDate('orders.created_at', '<=', request('end_date'));
        }
        if (request()->filled('cashier_id') && is_numeric(request('cashier_id'))) {
            $query->where('orders.cashier_id', (int) request('cashier_id'));
        }
        if (request()->filled('outlet_id') && is_numeric(request('outlet_id'))) {
            $query->where('orders.outlet_id', (int) request('outlet_id'));
        }
        if (in_array(request('payment_method'), ['cash', 'qris', 'debit', 'credit'])) {
            $query->where('orders.payment_method', request('payment_method'));
        }
        if (in_array(request('status_transaction'), ['normal', 'refunded', 'voided'])) {
            $query->where('orders.status_transaction', request('status_transaction'));
        }
    }

    /**
     * Detail transaksi.
     */
    public function show(Order $transaction)
    {
        abort_unless(auth()->user()->can('Transaction Access'), 403);

        $transaction->load([
            'items.product',
            'outlet',
            'cashier',
            'table',
            'customer',
            'promo',
            'voidedBy',
            'refundedBy',
        ]);

        return view('transactions.show', ['transaction' => $transaction]);
    }

    /**
     * Cetak ulang struk.
     */
    public function receipt(Order $transaction)
    {
        abort_unless(auth()->user()->can('Transaction Access'), 403);

        $transaction->load(['items', 'outlet', 'cashier', 'table', 'promo', 'customer']);

        return view('transactions.receipt', ['transaction' => $transaction]);
    }

    /**
     * Refund transaksi (kembalikan dana + stok).
     */
    public function refund(Request $request, Order $transaction)
    {
        abort_unless(auth()->user()->can('Transaction Refund'), 403);

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        try {
            $this->transactions->refund($transaction, $validated['reason']);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('transactions.show', $transaction->uuid)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('transactions.show', $transaction->uuid)
            ->with('success', "Transaction {$transaction->code_invoice} has been refunded.");
    }

    /**
     * Void transaksi (pembatalan + kembalikan stok).
     */
    public function void(Request $request, Order $transaction)
    {
        abort_unless(auth()->user()->can('Transaction Void'), 403);

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        try {
            $this->transactions->void($transaction, $validated['reason']);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('transactions.show', $transaction->uuid)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('transactions.show', $transaction->uuid)
            ->with('success', "Transaction {$transaction->code_invoice} has been voided.");
    }

    /**
     * Laporan pendapatan outlet aktif.
     */
    public function report(Request $request)
    {
        abort_unless(auth()->user()->can('Transaction Report'), 403);

        $startDate = $request->filled('start_date')
            ? $request->date('start_date')->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? $request->date('end_date')->endOfDay()
            : now()->endOfDay();

        $base = Order::whereBetween('created_at', [$startDate, $endDate]);

        if ($request->filled('cashier_id')) {
            $base->where('cashier_id', $request->cashier_id);
        }
        if ($request->filled('payment_method')) {
            $base->where('payment_method', $request->payment_method);
        }

        $valid = (clone $base)->valid();
        $voided = (clone $base)->where('status_transaction', 'voided');
        $refunded = (clone $base)->where('status_transaction', 'refunded');

        $summary = [
            'gross_sales' => (float) (clone $valid)->sum('subtotal'),
            'discount' => (float) (clone $valid)->sum('discount'),
            'tax' => (float) (clone $valid)->sum('tax'),
            'net_revenue' => (float) (clone $valid)->sum('grand_total'),
            'transaction_count' => (clone $valid)->count(),
            'refunded_total' => (float) $refunded->sum('grand_total'),
            'refunded_count' => $refunded->count(),
            'voided_total' => (float) $voided->sum('grand_total'),
            'voided_count' => $voided->count(),
        ];

        $summary['after_adjustment'] = $summary['net_revenue']
            - $summary['refunded_total'] - $summary['voided_total'];

        // Breakdown harian
        $daily = (clone $valid)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as transactions')
            ->selectRaw('SUM(subtotal) as gross_sales')
            ->selectRaw('SUM(discount) as discount')
            ->selectRaw('SUM(tax) as tax')
            ->selectRaw('SUM(grand_total) as net_revenue')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();

        // Breakdown per metode pembayaran
        $byPayment = (clone $valid)
            ->selectRaw('payment_method')
            ->selectRaw('COUNT(*) as transactions')
            ->selectRaw('SUM(grand_total) as total')
            ->groupBy('payment_method')
            ->get();

        return view('transactions.report', [
            'summary' => $summary,
            'daily' => $daily,
            'byPayment' => $byPayment,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'cashiers' => User::whereHas('orders')->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'cashier_id' => $request->cashier_id,
                'payment_method' => $request->payment_method,
            ],
        ]);
    }
}
