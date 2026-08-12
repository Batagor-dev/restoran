<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KitchenController extends Controller
{
    public function index()
    {
        // Ambil order dengan status pending atau processing
        $orders = Order::with(['items' => function ($query) {
            $query->where('kitchen_status', '!=', 'ready');
        }, 'items.product', 'outlet', 'cashier', 'table', 'customer'])
            ->whereIn('status_order', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();

        $this->data['orders'] = $orders;
        $this->data['pendingCount'] = Order::where('status_order', 'pending')->count();
        $this->data['processingCount'] = Order::where('status_order', 'processing')->count();

        return view('kitchen.index', $this->data);
    }

    public function updateStatus(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $status = $request->status;

            if (!in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid status'], 400);
            }

            if ($order->status_order === 'completed') {
                return response()->json(['status' => 'error', 'message' => 'Order already completed'], 400);
            }

            $order->status_order = $status;
            $order->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Order status updated to ' . $status,
                'order' => $order
            ]);

        } catch (\Exception $e) {
            \Log::error('Kitchen updateStatus error:', ['message' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ============ KDS FEATURES ============

    // 1. Update Item Status (Ready / Cooking)
    public function updateItemStatus(Request $request, $itemId)
    {
        try {
            $item = OrderItem::findOrFail($itemId);
            $status = $request->status;

            if (!in_array($status, ['pending', 'cooking', 'ready', 'served'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid item status'], 400);
            }

            $item->kitchen_status = $status;

            if ($status === 'ready') {
                $item->ready_at = now();
                $item->cooked_by = auth()->id();
            }

            $item->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Item status updated to ' . $status,
                'item' => $item
            ]);

        } catch (\Exception $e) {
            \Log::error('Kitchen updateItemStatus error:', ['message' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 2. History (Completed Orders)
    public function history()
    {
        $orders = Order::with(['items', 'outlet', 'cashier', 'table', 'customer'])
            ->where('status_order', 'completed')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        $this->data['orders'] = $orders;

        return view('kitchen.history', $this->data);
    }

    // 3. Get Orders untuk Notification (Polling)
    public function getNewOrders(Request $request)
    {
        $lastCheck = $request->last_check ?? now()->subMinutes(5);

        $newOrders = Order::with(['items' => function ($query) {
            $query->where('kitchen_status', 'pending');
        }, 'items.product', 'outlet', 'cashier', 'table', 'customer'])
            ->whereIn('status_order', ['pending', 'processing'])
            ->where('created_at', '>', $lastCheck)
            ->get();

        return response()->json([
            'status' => 'success',
            'orders' => $newOrders,
            'count' => $newOrders->count()
        ]);
    }

    public function show(Order $order)
    {
        $this->data['order'] = $order->load(['items.product', 'outlet', 'cashier', 'table', 'customer']);
        return view('kitchen.show', $this->data);
    }

    public function print(Order $order)
    {
        $this->data['order'] = $order->load(['items.product', 'outlet', 'cashier', 'table', 'customer']);
        return view('kitchen.print', $this->data);
    }
}