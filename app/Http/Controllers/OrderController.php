<?php

namespace App\Http\Controllers;

use App\DataTables\OrderDataTable;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(OrderDataTable $dataTable)
    {
        return $dataTable->render('orders.index');
    }

    /**
     * Resolve active outlet id, falling back to the first active outlet.
     */
    private function resolveOutletId(): ?int
    {
        $outletId = auth()->user()->current_outlet_id;

        if (! $outletId) {
            $firstOutlet = Outlet::where('status', true)->orderBy('id')->first();

            if ($firstOutlet) {
                auth()->user()->update(['current_outlet_id' => $firstOutlet->id]);
                $outletId = $firstOutlet->id;
            }
        }

        return $outletId;
    }

    public function create()
    {
        $outletId = $this->resolveOutletId();

        $this->data['tables'] = DiningTable::when($outletId, function ($query) use ($outletId) {
            return $query->where('outlet_id', $outletId);
        })->get();

        $this->data['products'] = Product::where('is_active', true)->get();
        $this->data['action'] = route('orders.store');

        return view('orders.form', $this->data);
    }

    public function store(StoreOrderRequest $request)
    {
        $outletId = $this->resolveOutletId();

        if (! $outletId) {
            return redirect()
                ->route('orders.index')
                ->with('error', 'No active outlet available. Please create an outlet first.');
        }

        try {
            DB::transaction(function () use ($request, $outletId) {
                $data = $request->validated();

                // Generate invoice code
                $data['code_invoice'] = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));
                $data['outlet_id'] = $outletId;
                $data['cashier_id'] = auth()->id();
                $data['table_id'] = $request->table_id ?? null;

                // Calculate totals
                $subtotal = 0;
                $items = $data['items'];

                foreach ($items as &$item) {
                    $product = Product::find($item['product_id']);

                    if (! $product) {
                        throw new \RuntimeException("Product #{$item['product_id']} not found.");
                    }

                    $item['product_name'] = $product->name;
                    $item['unit_price'] = $product->priceForOutlet($outletId);
                    $item['subtotal'] = round($item['quantity'] * $item['unit_price'], 2);
                    $item['discount_amount'] = 0;
                    $subtotal += $item['subtotal'];
                }

                $data['subtotal'] = round($subtotal, 2);
                $data['discount'] = 0;
                $data['tax'] = round($subtotal * 0.1, 2); // 10% tax
                $data['grand_total'] = round($subtotal + $data['tax'], 2);

                // Create order
                $order = Order::create($data);

                // Create order items
                foreach ($items as $item) {
                    $order->items()->create($item);
                }

                // Kurangi stok + audit trail (konsisten dengan alur POS)
                foreach ($items as $item) {
                    $this->decreaseStock($outletId, (int) $item['product_id'], (int) $item['quantity'], $order->code_invoice);
                }
            });

            return redirect()
                ->route('orders.index')
                ->with('success', 'Order has been created successfully!');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('orders.index')
                ->with('error', $e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $this->data['order'] = $order->load(['items.product', 'outlet', 'cashier', 'table', 'promo']);

        return view('orders.show', $this->data);
    }

    public function edit(Order $order)
    {
        $this->data['order'] = $order->load('items');
        $this->data['tables'] = DiningTable::all();
        $this->data['products'] = Product::where('is_active', true)->get();
        $this->data['action'] = route('orders.update', $order->uuid);

        return view('orders.form', $this->data);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        // Transaksi yang sudah di-refund/void tidak boleh diedit
        // (stoknya sudah dikembalikan oleh proses refund/void)
        if ($order->status_transaction !== 'normal') {
            return redirect()
                ->route('orders.index')
                ->with('error', "Cannot edit a {$order->status_transaction} transaction.");
        }

        try {
            DB::transaction(function () use ($request, $order) {
                $data = $request->validated();

                // Kembalikan stok item lama sebelum dihapus (delta adjustment)
                foreach ($order->items as $oldItem) {
                    $this->restoreStock($order->outlet_id, (int) $oldItem->product_id, (int) $oldItem->quantity, $order->code_invoice);
                }

                // Delete old items
                $order->items()->delete();

                // Calculate totals
                $subtotal = 0;
                $items = $data['items'];

                foreach ($items as &$item) {
                    $product = Product::find($item['product_id']);

                    if (! $product) {
                        throw new \RuntimeException("Product #{$item['product_id']} not found.");
                    }

                    $item['product_name'] = $product->name;
                    $item['unit_price'] = $product->priceForOutlet($order->outlet_id);
                    $item['subtotal'] = round($item['quantity'] * $item['unit_price'], 2);
                    $item['discount_amount'] = 0;
                    $subtotal += $item['subtotal'];
                }

                $data['subtotal'] = round($subtotal, 2);
                $data['discount'] = 0;
                $data['tax'] = round($subtotal * 0.1, 2);
                $data['grand_total'] = round($subtotal + $data['tax'], 2);

                // Update order
                $order->update($data);

                // Create new items
                foreach ($items as $item) {
                    $order->items()->create($item);
                }

                // Kurangi stok untuk item baru
                foreach ($items as $item) {
                    $this->decreaseStock($order->outlet_id, (int) $item['product_id'], (int) $item['quantity'], $order->code_invoice);
                }
            });

            return redirect()
                ->route('orders.index')
                ->with('success', 'Order has been updated successfully!');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('orders.index')
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Order $order)
    {
        try {
            DB::transaction(function () use ($order) {
                // Kembalikan stok HANYA untuk transaksi normal —
                // refunded/voided sudah pernah mendapat pengembalian stok.
                if ($order->status_transaction === 'normal') {
                    foreach ($order->items as $item) {
                        $this->restoreStock($order->outlet_id, (int) $item->product_id, (int) $item->quantity, $order->code_invoice);
                    }
                }

                $order->delete();
            });
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('orders.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order has been deleted successfully!');
    }

    /**
     * Kurangi stok produk pada outlet + catat StockMovement.
     */
    private function decreaseStock(int $outletId, int $productId, int $qty, string $invoice): void
    {
        /** @var ProductStock|null $stock */
        $stock = ProductStock::where('product_id', $productId)
            ->where('outlet_id', $outletId)
            ->lockForUpdate()
            ->first();

        // Produk tanpa record stok = tidak dikelola stoknya -> lewati
        if (! $stock) {
            return;
        }

        if ($stock->quantity < $qty) {
            throw new \RuntimeException(
                "Insufficient stock for '{$stock->product->name}'. Available: {$stock->quantity}, requested: {$qty}."
            );
        }

        $before = $stock->quantity;
        $after = $before - $qty;

        $stock->update(['quantity' => $after]);

        StockMovement::create([
            'outlet_id' => $outletId,
            'product_stock_id' => $stock->id,
            'movement_type' => 'out',
            'reference_type' => 'sale',
            'reference_id' => null,
            'quantity' => $qty,
            'stock_before' => $before,
            'stock_after' => $after,
            'notes' => "Sale {$invoice} (admin order)",
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Kembalikan stok produk pada outlet + catat StockMovement.
     */
    private function restoreStock(int $outletId, int $productId, int $qty, string $invoice): void
    {
        /** @var ProductStock|null $stock */
        $stock = ProductStock::where('product_id', $productId)
            ->where('outlet_id', $outletId)
            ->lockForUpdate()
            ->first();

        // Produk tanpa record stok = tidak dikelola stoknya -> lewati
        if (! $stock) {
            return;
        }

        $before = $stock->quantity;
        $after = $before + $qty;

        $stock->update(['quantity' => $after]);

        StockMovement::create([
            'outlet_id' => $outletId,
            'product_stock_id' => $stock->id,
            'movement_type' => 'return',
            'reference_type' => 'sale',
            'reference_id' => null,
            'quantity' => $qty,
            'stock_before' => $before,
            'stock_after' => $after,
            'notes' => "Return stock for order {$invoice}",
            'created_by' => auth()->id(),
        ]);
    }
}
