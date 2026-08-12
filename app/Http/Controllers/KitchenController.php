<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function index()
    {
        // Ambil order dengan status pending atau processing
        $orders = Order::with(['items.product', 'outlet', 'cashier'])
            ->whereIn('status_order', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();

        $this->data['orders'] = $orders;

        return view('kitchen.index', $this->data);
    }

    public function updateStatus(Request $request, $orderId)
    {
        try {
            // Cari order berdasarkan ID (bukan UUID)
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found'
                ], 404);
            }

            $status = $request->status;

            // Validasi status
            if (!in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid status: ' . $status
                ], 400);
            }

            // Cek apakah order sudah completed
            if ($order->status_order === 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order already completed'
                ], 400);
            }

            // Update status
            $order->status_order = $status;
            $order->save();

            \Log::info('Order status updated:', [
                'order_id' => $order->id,
                'new_status' => $status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Order status updated to ' . $status,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            \Log::error('Kitchen updateStatus error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
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

    public function getOrders()
    {
        $orders = Order::with(['items.product', 'outlet', 'cashier'])
            ->whereIn('status_order', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'orders' => $orders
        ]);
    }
}
