<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransactionService
{
    /**
     * Kembalikan seluruh stok item pada order (refund / void).
     * Produk tanpa record stok = tidak dikelola, dilewati.
     */
    public function restoreStockForOrder(Order $order, string $note): void
    {
        foreach ($order->items as $item) {
            /** @var ProductStock|null $stock */
            $stock = ProductStock::where('product_id', $item->product_id)
                ->where('outlet_id', $order->outlet_id)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                continue;
            }

            $before = $stock->quantity;
            $after = $before + $item->quantity;

            $stock->update(['quantity' => $after]);

            StockMovement::create([
                'outlet_id' => $order->outlet_id,
                'product_stock_id' => $stock->id,
                'movement_type' => 'return',
                'reference_type' => 'sale',
                'reference_id' => $order->id,
                'quantity' => $item->quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => $note,
                'created_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Refund transaksi: kembalikan stok + tandai refunded.
     */
    public function refund(Order $order, string $reason): void
    {
        if ($order->status_transaction !== 'normal') {
            throw new RuntimeException('Transaction has already been '.$order->status_transaction.'.');
        }

        DB::transaction(function () use ($order, $reason) {
            $this->restoreStockForOrder($order, "Refund {$order->code_invoice}");

            $order->update([
                'status_transaction' => 'refunded',
                'refund_reason' => $reason,
                'refunded_at' => now(),
                'refunded_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Void (pembatalan) transaksi: kembalikan stok + tandai voided.
     */
    public function void(Order $order, string $reason): void
    {
        if ($order->status_transaction !== 'normal') {
            throw new RuntimeException('Transaction has already been '.$order->status_transaction.'.');
        }

        DB::transaction(function () use ($order, $reason) {
            $this->restoreStockForOrder($order, "Void {$order->code_invoice}");

            $order->update([
                'status_transaction' => 'voided',
                'void_reason' => $reason,
                'voided_at' => now(),
                'voided_by' => auth()->id(),
            ]);
        });
    }
}
