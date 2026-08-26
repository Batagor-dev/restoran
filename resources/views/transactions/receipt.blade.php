<!DOCTYPE html>
<html>
<head>
    <title>Receipt - {{ $transaction->code_invoice }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; padding: 20px; max-width: 380px; margin: 0 auto; color: #0f172a; }
        .header { text-align: center; margin-bottom: 14px; border-bottom: 1px dashed #94a3b8; padding-bottom: 10px; }
        .header h1 { font-size: 15px; margin: 0; letter-spacing: 1px; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #64748b; font-size: 11px; }
        .info { margin-bottom: 10px; font-size: 11px; line-height: 1.6; }
        .info div { display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 11px; }
        th, td { padding: 3px 2px; text-align: left; vertical-align: top; }
        th { border-bottom: 1px solid #0f172a; }
        .right { text-align: right; white-space: nowrap; }
        .totals { border-top: 1px dashed #94a3b8; margin-top: 6px; padding-top: 6px; font-size: 11px; }
        .totals div { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .grand { font-size: 14px; font-weight: bold; margin-top: 4px; }
        .status { text-align: center; font-weight: bold; margin: 8px 0; font-size: 13px; letter-spacing: 2px; }
        .status.refunded { color: #be123c; }
        .footer { text-align: center; margin-top: 14px; border-top: 1px dashed #94a3b8; padding-top: 8px; color: #64748b; font-size: 10px; }
        @media print {
            body { padding: 5px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $transaction->outlet?->name ?? config('app.name') }}</h1>
        <p>{{ $transaction->outlet?->address ?? '' }}</p>
        <p>{{ $transaction->outlet?->phone ?? '' }}</p>
    </div>

    <div class="info">
        <div><span>Invoice</span><span>{{ $transaction->code_invoice }}</span></div>
        <div><span>Date</span><span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span></div>
        <div><span>Cashier</span><span>{{ $transaction->cashier?->name ?? '-' }}</span></div>
        <div><span>Customer</span><span>{{ $transaction->customer_name ?? 'Guest' }}</span></div>
        <div>
            <span>Type</span>
            <span>{{ $transaction->order_type === 'takeaway' ? 'TAKE AWAY' : 'DINE IN'
                . ($transaction->table ? ' - Table '.$transaction->table->number_table : '') }}</span>
        </div>
    </div>

    <table>
        @foreach($transaction->items as $item)
            <tr>
                <td colspan="2">{{ $item->product_name }} x{{ $item->quantity }}</td>
                <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($item->notes)
                <tr>
                    <td colspan="3" style="color:#64748b; font-style:italic;">&raquo; {{ $item->notes }}</td>
                </tr>
            @endif
        @endforeach
    </table>

    <div class="totals">
        <div><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
        @if($transaction->discount > 0)
            <div>
                <span>Voucher{{ $transaction->promo ? ' ('.$transaction->promo->name.')' : '' }}</span>
                <span>-Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
            </div>
        @endif
        <div><span>Tax (10%)</span><span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span></div>
        <div class="grand"><span>GRAND TOTAL</span><span>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span></div>
        <div style="margin-top:4px;"><span>Payment</span><span>{{ strtoupper($transaction->payment_method) }}</span></div>
    </div>

    @if($transaction->status_transaction !== 'normal')
        <div class="status {{ $transaction->status_transaction }}">
            ** {{ strtoupper($transaction->status_transaction) }} **
        </div>
        <div class="info">
            <div><span>At</span><span>{{ optional($transaction->status_transaction === 'voided' ? $transaction->voided_at : $transaction->refunded_at)->format('d/m/Y H:i') }}</span></div>
            <div><span>Reason</span><span>{{ $transaction->status_transaction === 'voided' ? $transaction->void_reason : $transaction->refund_reason }}</span></div>
        </div>
    @endif

    <div class="footer">
        <p>-- REPRINT --</p>
        <p>Printed on {{ now()->format('d/m/Y H:i') }} by {{ auth()->user()?->name }}</p>
        <p>Thank you for your visit!</p>
    </div>

    <div style="text-align:center; margin-top:16px;" class="no-print">
        <button onclick="window.print()" style="padding:8px 24px; background:#0f172a; color:white; border:none; border-radius:6px; cursor:pointer; font-family:sans-serif;">
            Print
        </button>
        <button onclick="window.close()" style="padding:8px 24px; background:#94a3b8; color:white; border:none; border-radius:6px; cursor:pointer; margin-left:8px; font-family:sans-serif;">
            Close
        </button>
    </div>
</body>
</html>
