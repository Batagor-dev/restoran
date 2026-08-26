<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\FavoriteProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\Promo;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $this->data['categories'] = ProductCategory::where('is_active', true)->orderBy('name')->get();
        $this->data['products'] = $this->getProducts($request);
        $this->data['favorites'] = $this->getFavorites();
        $this->data['tables'] = DiningTable::where('is_active', true)->orderBy('number_table')->get();
        $this->data['customers'] = Customer::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('pos.index', $this->data);
    }

    public function getProducts(Request $request)
    {
        $query = Product::where('is_active', true)->with([
            'category',
            'stocks' => fn ($q) => $q->where('outlet_id', $this->currentOutletId()),
            'favorites' => fn ($q) => $q->where('user_id', auth()->id()),
        ]);

        // Search dengan ILIKE untuk PostgreSQL (case-insensitive)
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', '%'.$request->search.'%');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Show favorites only
        if ($request->boolean('favorites')) {
            $query->favorite();
        }

        $products = $query->orderBy('name')->get();

        // Resolve effective price per active outlet
        $outletId = $this->currentOutletId();
        $products->each(function (Product $product) use ($outletId) {
            $product->effective_price = $product->priceForOutlet($outletId);
        });

        return response()->json($products);
    }

    public function getFavorites()
    {
        if (auth()->check()) {
            return Product::favorite()->with('category')->get();
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
        }

        FavoriteProduct::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json(['status' => 'added', 'message' => 'Product added to favorites']);
    }

    public function getCart(Request $request)
    {
        return response()->json(session()->get('pos_cart', []));
    }

    public function addToCart(Request $request)
    {
        $cart = session()->get('pos_cart', []);

        $product = Product::findOrFail($request->product_id);

        if (! $product->is_active) {
            return response()->json(['status' => 'error', 'message' => 'Product is not available.'], 422);
        }

        $quantity = $request->integer('quantity') ?: 1;
        $price = $product->priceForOutlet($this->currentOutletId());

        if ($price <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Product price is invalid.'], 422);
        }

        if (isset($cart[$request->product_id])) {
            $cart[$request->product_id]['quantity'] += $quantity;
            $cart[$request->product_id]['notes'] ??= '';
        } else {
            $cart[$request->product_id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'quantity' => $quantity,
                'image' => $product->image,
                'notes' => '',
                'subtotal' => $price * $quantity,
            ];
        }

        $cart[$request->product_id]['subtotal'] = $cart[$request->product_id]['price'] * $cart[$request->product_id]['quantity'];

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

        if (! isset($cart[$productId])) {
            return response()->json(['status' => 'error', 'message' => 'Item not found in cart.'], 422);
        }

        if ($request->quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['quantity'] = (int) $request->quantity;
            $cart[$productId]['subtotal'] = $cart[$productId]['price'] * $cart[$productId]['quantity'];
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
        session()->forget('pos_discount');

        return response()->json(['status' => 'success', 'message' => 'Cart cleared']);
    }

    public function applyPromo(Request $request)
    {
        $promoCode = trim((string) $request->promo_code);
        $cart = session()->get('pos_cart', []);

        if ($promoCode === '') {
            return response()->json(['status' => 'error', 'message' => 'Silakan masukkan kode promo terlebih dahulu.']);
        }

        $customerId = $request->filled('customer_id') ? (int) $request->customer_id : null;
        $total = $this->calculateTotal($cart);

        // Cari promo berdasarkan kode
        $promo = Promo::where(DB::raw('LOWER(name)'), strtolower($promoCode))
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (! $promo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode promo tidak ditemukan atau sudah tidak berlaku.',
            ]);
        }

        // 1. VALIDASI MINIMUM PURCHASE
        if ($promo->minimum_purchase && $total < $promo->minimum_purchase) {
            return response()->json([
                'status' => 'error',
                'message' => 'Minimum purchase for this promo is Rp '.number_format($promo->minimum_purchase, 0, ',', '.'),
            ]);
        }

        // 2. VALIDASI LIMIT PENGGUNAAN
        $limitError = $this->checkPromoUsageLimits($promo, $customerId);
        if ($limitError !== null) {
            return response()->json(['status' => 'error', 'message' => $limitError]);
        }

        // 2. VALIDASI SCOPE
        $validProductIds = [];

        switch ($promo->scope) {
            case 'order':
                $scopeMessage = 'Applied to all orders';
                break;

            case 'product':
                $promoProductIds = $promo->products()->pluck('products.id')->all();

                if (empty($promoProductIds)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This promo has no products selected',
                    ]);
                }

                foreach ($cart as $item) {
                    if (in_array($item['id'], $promoProductIds)) {
                        $validProductIds[] = $item['id'];
                    }
                }

                if (empty($validProductIds)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This promo only applies to specific products. Please add eligible products to your cart.',
                    ]);
                }

                $scopeMessage = 'Applied to selected products';
                break;

            case 'category_product':
                $categoryIds = $promo->categories()->pluck('product_categories.id')->all();

                if (empty($categoryIds)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This promo has no categories selected',
                    ]);
                }

                $eligible = Product::whereIn('id', array_keys($cart))
                    ->whereIn('category_id', $categoryIds)
                    ->pluck('id')
                    ->all();

                foreach ($cart as $item) {
                    if (in_array($item['id'], $eligible)) {
                        $validProductIds[] = $item['id'];
                    }
                }

                if (empty($validProductIds)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This promo only applies to specific product categories. Please add eligible products to your cart.',
                    ]);
                }

                $scopeMessage = 'Applied to selected categories';
                break;

            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid promo scope',
                ]);
        }

        // 3. HITUNG DISKON
        $discountValue = (float) $promo->discount_value;

        if ($promo->type === 'percentage') {
            $percentage = min($discountValue, 100);

            if ($promo->scope === 'order') {
                $discount = $total * ($percentage / 100);
            } else {
                $discount = $this->eligibleSubtotal($cart, $validProductIds) * ($percentage / 100);
            }
        } else {
            $base = $promo->scope === 'order'
                ? $total
                : $this->eligibleSubtotal($cart, $validProductIds);

            $discount = min($discountValue, $base);
        }

        $discount = round(max(0, $discount), 2);

        // Simpan ke session
        session()->put('pos_discount', [
            'promo_id' => $promo->id,
            'code' => $promo->name,
            'amount' => $discount,
            'type' => $promo->type,
            'scope' => $promo->scope,
            'eligible_product_ids' => array_values(array_unique($validProductIds)),
            'scope_message' => $scopeMessage,
        ]);

        return response()->json([
            'status' => 'success',
            'discount' => $discount,
            'total_after_discount' => max(0, $total - $discount),
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
            'message' => 'Promo removed successfully',
        ]);
    }

    public function processOrder(Request $request): JsonResponse
    {
        $outletId = $this->currentOutletId();

        if (! $outletId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active outlet selected. Please contact an administrator.',
            ], 422);
        }

        // Resolusi customer (opsional): dipakai untuk promo usage_per_customer & relasi order
        $customerId = null;

        if ($request->filled('customer_id')) {
            $customer = Customer::where('is_active', true)->find($request->customer_id);

            if (! $customer) {
                return response()->json(['status' => 'error', 'message' => 'Selected customer not found.'], 422);
            }

            $customerId = $customer->id;
        }

        $payloadItems = collect($request->input('items', []))
            ->filter(fn ($item) => ! empty($item['id']))
            ->values();

        if ($payloadItems->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Cart is empty.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($request, $payloadItems, $outletId, $customerId) {
                // --- Server-side recomputation (never trust client totals) ---
                $products = Product::whereIn('id', $payloadItems->pluck('id')->unique())
                    ->where('is_active', true)
                    ->with(['stocks' => fn ($q) => $q->where('outlet_id', $outletId)])
                    ->get()
                    ->keyBy('id');

                $lines = [];
                $subtotal = 0;

                foreach ($payloadItems as $item) {
                    $product = $products->get($item['id']);

                    if (! $product) {
                        throw new \RuntimeException("Product #{$item['id']} is not available.");
                    }

                    $qty = max(1, (int) ($item['quantity'] ?? 1));
                    $unitPrice = $product->priceForOutlet($outletId);
                    $lineSubtotal = round($unitPrice * $qty, 2);

                    $lines[] = [
                        'product' => $product,
                        'name' => $product->name,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $lineSubtotal,
                        'notes' => $item['notes'] ?? null,
                    ];

                    $subtotal += $lineSubtotal;
                }

                $subtotal = round($subtotal, 2);

                // Discount dari state session yang sudah tervalidasi
                $sessionDiscount = session('pos_discount');
                $discount = 0;
                $promoId = null;

                if ($sessionDiscount) {
                    // Kunci row promo untuk mencegah race condition pada usage limit
                    $promo = $sessionDiscount['promo_id']
                        ? Promo::lockForUpdate()->find($sessionDiscount['promo_id'])
                        : null;

                    if ($promo && $promo->is_active
                        && $promo->start_date->lte(now())
                        && $promo->end_date->gte(now())) {

                        // Validasi ulang limit penggunaan secara presisi (final gate)
                        $limitError = $this->checkPromoUsageLimits($promo, $customerId);

                        if ($limitError !== null) {
                            throw new \RuntimeException($limitError);
                        }

                        $discount = min((float) $sessionDiscount['amount'], $subtotal);

                        // Re-validate scoped promo against final cart
                        $eligibleIds = $sessionDiscount['eligible_product_ids'] ?? [];
                        if ($sessionDiscount['scope'] !== 'order' && empty($eligibleIds)) {
                            $discount = 0;
                        }

                        $promoId = $discount > 0 ? $promo->id : null;
                    }
                }

                $tax = round($subtotal * 0.1, 2); // 10% tax, konsisten dengan frontend
                $grandTotal = round(max(0, $subtotal - $discount + $tax), 2);

                // --- Create order ---
                $invoice = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));

                $customer = $customerId ? Customer::find($customerId) : null;

                $order = Order::create([
                    'code_invoice' => $invoice,
                    'outlet_id' => $outletId,
                    'cashier_id' => auth()->id(),
                    'table_id' => $request->input('table_id') ?: null,
                    'customer_id' => $customerId,
                    'customer_name' => $request->input('customer_name') ?: ($customer->name ?? 'Guest'),
                    'promo_id' => $promoId,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'grand_total' => $grandTotal,
                    'payment_method' => in_array($request->input('payment_method'), ['cash', 'qris', 'debit', 'credit'])
                        ? $request->input('payment_method')
                        : 'cash',
                    'order_type' => in_array($request->input('order_type'), ['dine_in', 'takeaway'])
                        ? $request->input('order_type')
                        : 'dine_in',
                    'status_order' => 'pending',
                ]);

                // --- Create order items ---
                foreach ($lines as $line) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $line['product']->id,
                        'product_name' => $line['name'],
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'discount_amount' => 0,
                        'subtotal' => $line['subtotal'],
                        'notes' => $line['notes'],
                    ]);
                }

                // --- Validate + decrement stock with audit trail ---
                foreach ($lines as $line) {
                    /** @var ProductStock|null $stock */
                    $stock = ProductStock::where('product_id', $line['product']->id)
                        ->where('outlet_id', $outletId)
                        ->lockForUpdate()
                        ->first();

                    // Produk tanpa record stok = tidak dikelola stoknya -> boleh dijual
                    if (! $stock) {
                        continue;
                    }

                    if ($stock->quantity < $line['quantity']) {
                        throw new \RuntimeException(
                            "Insufficient stock for '{$line['name']}'. Available: {$stock->quantity}, requested: {$line['quantity']}."
                        );
                    }

                    $stockBefore = $stock->quantity;
                    $stockAfter = $stockBefore - $line['quantity'];

                    $stock->update(['quantity' => $stockAfter]);

                    StockMovement::create([
                        'outlet_id' => $outletId,
                        'product_stock_id' => $stock->id,
                        'movement_type' => 'out',
                        'reference_type' => 'sale',
                        'reference_id' => $order->id,
                        'quantity' => $line['quantity'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'notes' => "Sale {$invoice}",
                        'created_by' => auth()->id(),
                    ]);
                }

                // --- Cleanup session ---
                session()->forget('pos_cart');
                session()->forget('pos_discount');

                return ['invoice' => $invoice, 'order_uuid' => $order->uuid];
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => $e instanceof \RuntimeException
                    ? $e->getMessage()
                    : 'Failed to process order. Please try again.',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'invoice' => $result['invoice'],
            'order_uuid' => $result['order_uuid'],
        ]);
    }

    protected function calculateTotal(array $cart): float
    {
        return round(collect($cart)->sum('subtotal'), 2);
    }

    protected function eligibleSubtotal(array $cart, array $validProductIds): float
    {
        $sum = 0;

        foreach ($cart as $item) {
            if (in_array($item['id'], $validProductIds)) {
                $sum += $item['subtotal'];
            }
        }

        return $sum;
    }

    /**
     * Validasi usage_limit (total) dan usage_per_customer secara presisi
     * berdasarkan relasi orders.promo_id.
     * Return null jika lolos, atau pesan error.
     */
    protected function checkPromoUsageLimits(Promo $promo, ?int $customerId): ?string
    {
        if ($promo->usage_limit && $promo->orders()->count() >= $promo->usage_limit) {
            return 'This promo has reached its total usage limit.';
        }

        if ($promo->usage_per_customer && $customerId) {
            $used = $promo->orders()->where('customer_id', $customerId)->count();

            if ($used >= $promo->usage_per_customer) {
                return 'This promo has reached its usage limit for this customer.';
            }
        }

        return null;
    }

    /**
     * Resolve the active outlet for the current session.
     */
    protected function currentOutletId(): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return $user->current_outlet_id;
    }
}
