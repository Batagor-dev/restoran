<?php

namespace App\Http\Controllers;

use App\DataTables\StockMovementDataTable;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\ProductStock;
use App\Models\StockMovement;

class StockMovementController extends Controller
{
    public function index(StockMovementDataTable $dataTable)
    {
        return $dataTable->render('stock-movements.index');
    }

    public function create()
{
    // Ambil data product stock dengan format yang sesuai untuk select2
    $stocks = ProductStock::with(['product', 'outlet'])->get()->map(function($stock) {
        return [
            'value' => $stock->id,
            'label' => $stock->product->name . ' - ' . $stock->outlet->name . ' (' . $stock->quantity . ')'
        ];
    });

    $this->data['stocks'] = $stocks;
    $this->data['action'] = route('stock-movements.store');

    return view('stock-movements.form', $this->data);
}

    public function store(StoreStockMovementRequest $request)
    {
        $data = $request->validated();

        // Get current stock
        $stock = ProductStock::findOrFail($data['product_stock_id']);
        $stockBefore = $stock->quantity;

        // Calculate stock after
        $quantity = $data['quantity'];
        $movementType = $data['movement_type'];

        if ($movementType === 'in' || $movementType === 'return') {
            $stockAfter = $stockBefore + $quantity;
        } elseif ($movementType === 'out') {
            if ($stockBefore < $quantity) {
                return redirect()
                    ->route('stock-movements.index')
                    ->with('error', 'Insufficient stock! Available: ' . $stockBefore);
            }
            $stockAfter = $stockBefore - $quantity;
        } else {
            // adjustment
            $stockAfter = $quantity;
        }

        // Create stock movement
        StockMovement::create([
            'product_stock_id' => $data['product_stock_id'],
            'movement_type' => $movementType,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Update stock quantity
        $stock->update(['quantity' => $stockAfter]);

        return redirect()
            ->route('stock-movements.index')
            ->with('success', 'Stock movement has been created successfully!');
    }

    public function destroy(StockMovement $stockMovement)
    {
        $stockMovement->delete();

        return redirect()
            ->route('stock-movements.index')
            ->with('success', 'Stock movement has been deleted successfully!');
    }
}