<?php

namespace App\Http\Controllers;

use App\DataTables\OrderDataTable;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\DiningTable;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(OrderDataTable $dataTable)
    {
        return $dataTable->render('orders.index');
    }

    public function create()
{
    // Ambil outlet pertama sebagai default jika user tidak punya current_outlet_id
    $outletId = auth()->user()->current_outlet_id;
    
    if (!$outletId) {
        // Jika user tidak punya outlet, ambil outlet pertama yang aktif
        $firstOutlet = Outlet::where('is_active', true)->first();
        if ($firstOutlet) {
            $outletId = $firstOutlet->id;
            // Simpan ke user agar tidak perlu ambil lagi
            auth()->user()->update(['current_outlet_id' => $firstOutlet->id]);
        }
    }

    $this->data['tables'] = DiningTable::when($outletId, function ($query) use ($outletId) {
        return $query->where('outlet_id', $outletId);
    })->get();

    $this->data['products'] = Product::where('is_active', true)->get();
    $this->data['action'] = route('orders.store');

    return view('orders.form', $this->data);
}

    public function store(StoreOrderRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            // Generate invoice code
            $data['code_invoice'] = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $data['outlet_id'] = auth()->user()->current_outlet_id ?? 1;
            $data['cashier_id'] = auth()->id();
            $data['table_id'] = $request->table_id ?? null;

            // Calculate totals
            $subtotal = 0;
            $totalDiscount = 0;
            $items = $data['items'];

            foreach ($items as &$item) {
                $product = Product::find($item['product_id']);
                $item['product_name'] = $product->name;
                $item['unit_price'] = $product->price;
                $item['subtotal'] = $item['quantity'] * $product->price;
                $item['discount_amount'] = 0;
                $subtotal += $item['subtotal'];
            }

            $data['subtotal'] = $subtotal;
            $data['discount'] = 0;
            $data['tax'] = round($subtotal * 0.1, 2); // 10% tax
            $data['grand_total'] = $subtotal + $data['tax'];

            // Create order
            $order = Order::create($data);

            // Create order items
            foreach ($items as $item) {
                $order->items()->create($item);
            }

            return redirect()
                ->route('orders.index')
                ->with('success', 'Order has been created successfully!');
        });
    }

    public function show(Order $order)
    {
        $this->data['order'] = $order->load(['items.product', 'outlet', 'cashier', 'table']);
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
        return DB::transaction(function () use ($request, $order) {
            $data = $request->validated();

            // Delete old items
            $order->items()->delete();

            // Calculate totals
            $subtotal = 0;
            $items = $data['items'];

            foreach ($items as &$item) {
                $product = Product::find($item['product_id']);
                $item['product_name'] = $product->name;
                $item['unit_price'] = $product->price;
                $item['subtotal'] = $item['quantity'] * $product->price;
                $item['discount_amount'] = 0;
                $subtotal += $item['subtotal'];
            }

            $data['subtotal'] = $subtotal;
            $data['discount'] = 0;
            $data['tax'] = round($subtotal * 0.1, 2);
            $data['grand_total'] = $subtotal + $data['tax'];

            // Update order
            $order->update($data);

            // Create new items
            foreach ($items as $item) {
                $order->items()->create($item);
            }

            return redirect()
                ->route('orders.index')
                ->with('success', 'Order has been updated successfully!');
        });
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()
            ->route('orders.index')
            ->with('success', 'Order has been deleted successfully!');
    }
}