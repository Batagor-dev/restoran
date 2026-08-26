<?php

namespace App\Http\Controllers;

use App\DataTables\ProductStockDataTable;
use App\Http\Requests\StoreProductStockRequest;
use App\Http\Requests\UpdateProductStockRequest;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;

class ProductStockController extends Controller
{
    public function index(ProductStockDataTable $dataTable)
    {
        return $dataTable->render('product-stocks.index');
    }

    public function create()
    {
        // Ambil data produk dengan format yang sesuai untuk select2
        $products = Product::where('is_active', true)->get()->map(function ($product) {
            return [
                'value' => $product->id,
                'label' => $product->name,
            ];
        });

        $outlets = Outlet::all()->map(function ($outlet) {
            return [
                'value' => $outlet->id,
                'label' => $outlet->name,
            ];
        });

        $this->data['products'] = $products;
        $this->data['outlets'] = $outlets;
        $this->data['action'] = route('product-stocks.store');

        return view('product-stocks.form', $this->data);
    }

    public function store(StoreProductStockRequest $request)
    {
        $data = $request->validated();

        // Karyawan non Super Admin/Owner hanya boleh mengelola stok outlet aktifnya
        if (! auth()->user()->hasRole(['Super Admin', 'Owner'])) {
            $data['outlet_id'] = auth()->user()->current_outlet_id;
        }

        // Check if stock already exists for this product & outlet
        $existing = ProductStock::where('product_id', $data['product_id'])
            ->where('outlet_id', $data['outlet_id'])
            ->first();

        if ($existing) {
            return redirect()
                ->route('product-stocks.index')
                ->with('error', 'Stock already exists for this product in this outlet. Please edit the existing stock.');
        }

        ProductStock::create($data);

        return redirect()
            ->route('product-stocks.index')
            ->with('success', 'Product stock has been created successfully!');
    }

    public function edit(ProductStock $productStock)
    {
        $this->data['productStock'] = $productStock;
        $this->data['action'] = route('product-stocks.update', $productStock->uuid);

        return view('product-stocks.form', $this->data);
    }

    public function update(UpdateProductStockRequest $request, ProductStock $productStock)
    {
        $data = $request->validated();
        $data['price'] = $data['price'] ?? null;

        // Log stock movement if quantity changes
        if ($data['quantity'] != $productStock->quantity) {
            $this->logStockMovement($productStock, (int) $data['quantity'], 'adjustment');
        }

        $productStock->update($data);

        return redirect()
            ->route('product-stocks.index')
            ->with('success', 'Product stock has been updated successfully!');
    }

    public function destroy(ProductStock $productStock)
    {
        $productStock->delete();

        return redirect()
            ->route('product-stocks.index')
            ->with('success', 'Product stock has been deleted successfully!');
    }

    protected function logStockMovement(ProductStock $stock, int $newQuantity, string $type)
    {
        $stockBefore = $stock->quantity;
        $stockAfter = $newQuantity;
        $quantityDiff = $newQuantity - $stockBefore;

        StockMovement::create([
            'outlet_id' => $stock->outlet_id,
            'product_stock_id' => $stock->id,
            'movement_type' => $type,
            'reference_type' => 'adjustment',
            'reference_id' => null,
            'quantity' => abs($quantityDiff),
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => 'Auto-logged from stock update',
            'created_by' => auth()->id(),
        ]);
    }
}
