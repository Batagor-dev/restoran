<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\FavoriteProduct;
use App\Models\Promo;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\DiningTable;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $this->data['categories'] = ProductCategory::where('is_active', true)->get();
        $this->data['products'] = $this->getProducts($request);
        $this->data['favorites'] = $this->getFavorites();
        $this->data['promos'] = Promo::where('is_active', true)->get();
        $this->data['customers'] = Customer::where('is_active', true)->get();
        $this->data['tables'] = DiningTable::where('is_active', true)->get();
        // $this->data['customers'] = Customer::all(); // COMMENT KARENA MODEL CUSTOMER BELUM ADA

        return view('pos.index', $this->data);
    }

    public function getProducts(Request $request)
    {
        $query = Product::where('is_active', true)->with('category');

        // Search dengan ILIKE untuk PostgreSQL (case-insensitive)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'ILIKE', '%' . $search . '%');
        }

        // Filter by category
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category_id', $request->category);
        }

        // Show favorites only
        if ($request->has('favorites') && $request->favorites) {
            $query->favorite();
        }

        return $query->get();
    }

    public function getFavorites()
    {
        if (auth()->check()) {
            return Product::favorite()->get();
        }
        return collect();
    }

    public function toggleFavorite(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $user = auth()->user();

        $favorite = FavoriteProduct::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed', 'message' => 'Product removed from favorites']);
        } else {
            FavoriteProduct::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
            return response()->json(['status' => 'added', 'message' => 'Product added to favorites']);
        }
    }

    public function getCart(Request $request)
    {
        $cart = session()->get('pos_cart', []);
        return response()->json($cart);
    }

    public function addToCart(Request $request)
    {
        $cart = session()->get('pos_cart', []);

        $product = Product::findOrFail($request->product_id);

        if (isset($cart[$request->product_id])) {
            $cart[$request->product_id]['quantity'] += $request->quantity ?? 1;
        } else {
            $cart[$request->product_id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $request->quantity ?? 1,
                'image' => $product->image,
                'subtotal' => $product->price * ($request->quantity ?? 1),
            ];
        }

        session()->put('pos_cart', $cart);

        return response()->json([
            'status' => 'success',
            'cart' => $cart,
            'total' => $this->calculateTotal($cart),
        ]);
    }

    public function updateCart(Request $request)
    {
        $cart = session()->get('pos_cart', []);
        $productId = $request->product_id;

        if ($request->quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['quantity'] = $request->quantity;
            $cart[$productId]['subtotal'] = $cart[$productId]['price'] * $request->quantity;
        }

        session()->put('pos_cart', $cart);

        return response()->json([
            'status' => 'success',
            'cart' => $cart,
            'total' => $this->calculateTotal($cart),
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $cart = session()->get('pos_cart', []);
        unset($cart[$request->product_id]);
        session()->put('pos_cart', $cart);

        return response()->json([
            'status' => 'success',
            'cart' => $cart,
            'total' => $this->calculateTotal($cart),
        ]);
    }

    public function clearCart()
    {
        session()->forget('pos_cart');
        return response()->json(['status' => 'success', 'message' => 'Cart cleared']);
    }

    public function applyPromo(Request $request)
    {
        $promoCode = $request->promo_code;
        $cart = session()->get('pos_cart', []);
        $total = $this->calculateTotal($cart);

        // Cari promo berdasarkan kode
        $promo = Promo::where('name', $promoCode)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$promo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode promo tidak ditemukan atau sudah tidak berlaku.'
            ]);
        }

        // 1. VALIDASI MINIMUM PURCHASE
        if ($promo->minimum_purchase && $total < $promo->minimum_purchase) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimum purchase for this promo is Rp ' . number_format($promo->minimum_purchase, 0, ',', '.')
            ]);
        }

        // 2. VALIDASI SCOPE
        $isValid = true;
        $scopeMessage = '';

        switch ($promo->scope) {
            case 'order':
                // Promo berlaku untuk semua order
                $isValid = true;
                $scopeMessage = 'Applied to all orders';
                break;

            case 'product':
                // Promo hanya berlaku untuk produk tertentu
                // Asumsi: ada kolom product_id di tabel promos (atau pivot table)
                $productIds = $promo->products()->pluck('product_id')->toArray();

                if (empty($productIds)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This promo has no products selected'
                    ]);
                }

                $validProducts = [];
                foreach ($cart as $item) {
                    if (in_array($item['id'], $productIds)) {
                        $validProducts[] = $item['id'];
                    }
                }

                if (empty($validProducts)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This promo only applies to specific products. Please add eligible products to your cart.'
                    ]);
                }

                $isValid = true;
                $scopeMessage = 'Applied to selected products';
                break;

            case 'category_product':
                // Promo hanya berlaku untuk kategori tertentu
                $categoryIds = $promo->categories()->pluck('category_id')->toArray();

                if (empty($categoryIds)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This promo has no categories selected'
                    ]);
                }

                $validProducts = [];
                foreach ($cart as $item) {
                    $product = Product::find($item['id']);
                    if ($product && in_array($product->category_id, $categoryIds)) {
                        $validProducts[] = $item['id'];
                    }
                }

                if (empty($validProducts)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This promo only applies to specific product categories. Please add eligible products to your cart.'
                    ]);
                }

                $isValid = true;
                $scopeMessage = 'Applied to selected categories';
                break;

            default:
                $isValid = false;
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid promo scope'
                ]);
        }

        // 3. HITUNG DISKON
        $discount = 0;

        if ($promo->type == 'percentage') {
            // Diskon persentase (hanya dari produk yang eligible)
            if ($promo->scope === 'order') {
                $discount = $total * ($promo->discount_value / 100);
            } else {
                // Hitung subtotal dari produk yang eligible saja
                $eligibleTotal = 0;
                foreach ($cart as $item) {
                    if (in_array($item['id'], $validProducts)) {
                        $eligibleTotal += $item['subtotal'];
                    }
                }
                $discount = $eligibleTotal * ($promo->discount_value / 100);
            }
        } else {
            // Diskon nominal (fixed)
            $discount = $promo->discount_value;

            // Kalau scope bukan order, hanya potong dari subtotal eligible
            if ($promo->scope !== 'order') {
                $eligibleTotal = 0;
                foreach ($cart as $item) {
                    if (in_array($item['id'], $validProducts)) {
                        $eligibleTotal += $item['subtotal'];
                    }
                }
                $discount = min($discount, $eligibleTotal);
            } else {
                $discount = min($discount, $total);
            }
        }

        // Simpan ke session
        session()->put('pos_discount', [
            'promo_id' => $promo->id,
            'code' => $promo->name,
            'amount' => $discount,
            'type' => $promo->type,
            'scope' => $promo->scope,
            'scope_message' => $scopeMessage,
        ]);

        return response()->json([
            'status' => 'success',
            'discount' => $discount,
            'total_after_discount' => $total - $discount,
            'message' => 'Promo applied successfully!',
            'scope_message' => $scopeMessage,
        ]);
    }

    public function removePromo(Request $request)
    {
        session()->forget('pos_discount');

        return response()->json([
            'status' => 'success',
            'discount' => 0,
            'message' => 'Promo removed successfully'
        ]);
    }

    protected function calculateTotal($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }

    public function processOrder(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->all();

            // DEBUG: Log data yang masuk
            \Log::info('Order Data:', $data);

            // Generate invoice
            $invoice = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Create order
            $order = Order::create([
                'uuid' => (string) Str::uuid(),
                'code_invoice' => $invoice,
                'outlet_id' => auth()->user()->current_outlet_id ?? 1,
                'cashier_id' => auth()->id(),
                'customer_id' => $data['customer_id'] ?? null,
                'table_id' => $data['table_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? $data['customer'] ?? null,
                'subtotal' => $data['subtotal'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'grand_total' => $data['grand_total'] ?? 0,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'status_order' => 'pending',
            ]);

            // Create order items
            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'uuid' => (string) Str::uuid(),
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'discount_amount' => 0,
                    'subtotal' => $item['price'] * $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            // Update stock
            foreach ($data['items'] as $item) {
                $product = Product::find($item['id']);
                if ($product) {
                    $stock = ProductStock::where('product_id', $product->id)
                        ->where('outlet_id', auth()->user()->current_outlet_id ?? 1)
                        ->first();
                    if ($stock) {
                        $stock->decrement('quantity', $item['quantity']);
                    }
                }
            }

            // Clear session cart
            session()->forget('pos_cart');

            return response()->json([
                'status' => 'success',
                'invoice' => $invoice,
                'order_id' => $order->id,
            ]);
        });
    }
}
