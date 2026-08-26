<!DOCTYPE html>
<html>
<head>
    <title>Transactions Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; padding: 20px; color: #0f172a; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #64748b; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; }
        th { background: #f1f5f9; }
        .right { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 6px; }
            th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">
    <h1>Transaction History</h1>
    <p class="meta">
        Outlet: {{ auth()->user()->currentOutlet?->name ?? 'All (active outlet)' }}
        &middot; Generated: {{ $generatedAt->format('d M Y H:i') }} by {{ auth()->user()->name }}
        &middot; Total rows: {{ $transactions->count() }}
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Invoice</th>
                <th>Date</th>
                <th>Cashier</th>
                <th>Customer</th>
                <th>Type</th>
                <th>Payment</th>
                <th class="right">Subtotal</th>
                <th class="right">Discount</th>
                <th class="right">Tax</th>
                <th class="right">Grand Total</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->code_invoice }}</td>
                    <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $row->cashier?->name ?? '-' }}</td>
                    <td>{{ $row->customer_name ?? '-' }}</td>
                    <td>{{ $row->order_type === 'takeaway' ? 'Take Away' : 'Dine In' }}</td>
                    <td class="center">{{ strtoupper($row->payment_method) }}</td>
                    <td class="right">{{ number_format($row->subtotal, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($row->discount, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($row->tax, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($row->grand_total, 2, ',', '.') }}</td>
                    <td class="center">{{ ucfirst($row->status_transaction === 'normal' ? 'success' : $row->status_transaction) }}</td>
                </tr>
            @empty
                <tr><td colspan="12" class="center">No transactions found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:16px;" class="no-print">
        <button onclick="window.print()" style="padding:8px 24px; background:#0f172a; color:white; border:none; border-radius:6px; cursor:pointer;">
            Print / Save as PDF
        </button>
        <button onclick="window.close()" style="padding:8px 24px; background:#94a3b8; color:white; border:none; border-radius:6px; cursor:pointer; margin-left:8px;">
            Close
        </button>
    </div>
</body>
</html>
