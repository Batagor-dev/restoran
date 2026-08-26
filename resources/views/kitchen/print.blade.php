<!DOCTYPE html>
<html>
<head>
    <title>Kitchen Order - {{ $order->code_invoice }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { margin: 2px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        .total { font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; color: #999; font-size: 10px; }
        .order-info { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .order-info div { flex: 1; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; }
        .badge-pending { background: #fffbeb; color: #b45309; }
        .badge-processing { background: #eff6ff; color: #1d4ed8; }
        .badge-completed { background: #ecfdf5; color: #047857; }
        .badge-cancelled { background: #fff1f2; color: #be123c; }
        @media print {
            .no-print { display: none; }
            body { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KITCHEN ORDER</h1>
        <p>{{ $order->code_invoice }}</p>
        <p>{{ $order->created_at->format('d M Y H:i') }}</p>
    </div>

    <div class="order-info">
        <div>
            <strong>Outlet:</strong> {{ $order->outlet?->name ?? '-' }}
        </div>
        <div>
            <strong>Type:</strong> {{ $order->order_type === 'takeaway' ? 'Take Away' : 'Dine In' }}
        </div>
        <div>
            <strong>Table:</strong> {{ $order->table?->number_table ?? '-' }}
        </div>
        <div>
            <strong>Customer:</strong> {{ $order->customer_name ?? '-' }}
        </div>
        <div>
            <strong>Status:</strong>
            <span class="badge badge-{{ $order->status_order }}">
                {{ ucfirst($order->status_order) }}
            </span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th style="text-align:right">Qty</th>
                <th style="text-align:right">Price</th>
                <th style="text-align:right">Subtotal</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td style="text-align:right">{{ $item->quantity }}</td>
                    <td style="text-align:right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align:right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    <td>{{ $item->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="text-align:right">
        <p><strong>Subtotal:</strong> Rp {{ number_format($order->subtotal, 0, ',', '.') }}</p>
        <p><strong>Tax:</strong> Rp {{ number_format($order->tax, 0, ',', '.') }}</p>
        <p style="font-size:16px"><strong>Grand Total:</strong> Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
    </div>

    <div class="footer">
        <p>Printed on {{ now()->format('d M Y H:i') }} | {{ config('app.name') }}</p>
    </div>

    <div style="text-align:center; margin-top:20px;" class="no-print">
        <button onclick="window.print()" style="padding:8px 20px; background:#333; color:white; border:none; border-radius:4px; cursor:pointer;">
            🖨️ Print
        </button>
        <button onclick="window.close()" style="padding:8px 20px; background:#999; color:white; border:none; border-radius:4px; cursor:pointer; margin-left:10px;">
            Close
        </button>
    </div>
</body>
</html>