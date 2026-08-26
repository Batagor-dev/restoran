<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\Promo;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class PosOrderTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Product $product;

    private function makeUser(): User
    {
        $outlet = Outlet::create([
            'name' => 'Outlet Test '.uniqid(),
            'status' => true,
        ]);

        $category = ProductCategory::create([
            'name' => 'Cat Test '.uniqid(),
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Product Test '.uniqid(),
            'price' => 10000,
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Kasir Test',
            'username' => 'kasir_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $outlet->id,
        ]);
        // email_verified_at bukan bagian dari $fillable User
        $user->forceFill(['email_verified_at' => now()])->save();

        // Karyawan wajib ter-assign ke outlet (dicek SetDefaultOutletMiddleware)
        $user->outlets()->attach($outlet->id);

        return $user;
    }

    public function test_process_order_creates_order_and_audits_stock(): void
    {
        $this->user = $this->makeUser();
        $stock = ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $this->product->id,
            'outlet_id' => $this->user->current_outlet_id,
            'quantity' => 5,
        ]);

        $response = $this->actingAs($this->user)->postJson('/pos/order', [
            'customer_name' => 'Budi',
            'items' => [
                ['id' => $this->product->id, 'quantity' => 2, 'notes' => 'pedas'],
            ],
            'payment_method' => 'cash',
            // Nilai client sengaja dipalsukan, server harus menghitung ulang
            'subtotal' => 999999,
            'grand_total' => 1,
        ]);

        if (! $response->isOk()) {
            fwrite(STDERR, 'DEBUG RESPONSE: '.$response->getContent().PHP_EOL);
        }

        $response->assertOk()->assertJson(['status' => 'success']);
        // Global scope BelongsToOutlet mem-filter ke outlet test saja
        $this->assertEquals(1, Order::count());

        $order = Order::first();
        $this->assertEquals(20000, (float) $order->subtotal);
        $this->assertEquals(2000, (float) $order->tax);
        $this->assertEquals(22000, (float) $order->grand_total);
        $this->assertEquals('pending', $order->status_order);
        $this->assertEquals($this->user->current_outlet_id, $order->outlet_id);

        $stock->refresh();
        $this->assertEquals(3, $stock->quantity);

        $movement = StockMovement::where('reference_type', 'sale')
            ->where('product_stock_id', $stock->id)
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(5, $movement->stock_before);
        $this->assertEquals(3, $movement->stock_after);

        // Cart & promo session harus bersih
        $this->assertNull(session('pos_cart'));
        $this->assertNull(session('pos_discount'));
    }

    public function test_insufficient_stock_is_rejected(): void
    {
        $this->user = $this->makeUser();
        $stock = ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $this->product->id,
            'outlet_id' => $this->user->current_outlet_id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->user)->postJson('/pos/order', [
            'items' => [
                ['id' => $this->product->id, 'quantity' => 10],
            ],
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422)->assertJson(['status' => 'error']);

        $stock->refresh();
        $this->assertEquals(1, $stock->quantity);
        // Scoped ke outlet test lewat global scope
        $this->assertEquals(0, Order::count());
    }

    public function test_outlet_specific_price_overrides_global_price(): void
    {
        $this->user = $this->makeUser();
        $stock = ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $this->product->id,
            'outlet_id' => $this->user->current_outlet_id,
            'quantity' => 10,
            'price' => 12000,
        ]);

        $response = $this->actingAs($this->user)->postJson('/pos/order', [
            'items' => [
                ['id' => $this->product->id, 'quantity' => 1],
            ],
            'payment_method' => 'qris',
        ]);

        if (! $response->isOk()) {
            fwrite(STDERR, 'DEBUG PRICE TEST: '.$response->getContent().PHP_EOL);
        }

        $response->assertOk();

        $order = Order::first();
        $item = OrderItem::where('order_id', $order->id)->first();
        $this->assertEquals(12000, (float) $item->unit_price);
        $this->assertEquals(13200, (float) $order->grand_total);
    }

    public function test_scoped_product_promo_applies_discount(): void
    {
        $this->user = $this->makeUser();
        ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $this->product->id,
            'outlet_id' => $this->user->current_outlet_id,
            'quantity' => 10,
        ]);

        $promo = Promo::create([
            'name' => 'HEMATTEST'.uniqid(),
            'scope' => 'product',
            'type' => 'percentage',
            'discount_value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);
        $promo->products()->sync([$this->product->id]);

        $this->actingAs($this->user)
            ->postJson('/pos/cart/add', ['product_id' => $this->product->id])
            ->assertOk();

        $apply = $this->postJson('/pos/promo/apply', ['promo_code' => $promo->name]);

        if (! $apply->isOk() || $apply->json('status') !== 'success') {
            fwrite(STDERR, 'DEBUG PROMO APPLY: '.$apply->getContent().PHP_EOL);
        }

        $apply->assertOk()->assertJson(['status' => 'success']);
        $this->assertEquals(1000, round((float) $apply->json('discount'), 2));

        $process = $this->postJson('/pos/order', [
            'items' => [
                ['id' => $this->product->id, 'quantity' => 1],
            ],
            'payment_method' => 'cash',
        ]);

        if (! $process->isOk()) {
            fwrite(STDERR, 'DEBUG PROMO PROCESS: '.$process->getContent().PHP_EOL);
        }

        $process->assertOk();

        $order = Order::first();
        $this->assertEquals(10000, (float) $order->subtotal);
        $this->assertEquals(1000, (float) $order->discount);
        // grandTotal = subtotal - discount + tax(10%) => 10000 - 1000 + 1000
        $this->assertEquals(10000, (float) $order->grand_total);

        // promo_id harus tercatat di order untuk usage tracking
        $this->assertEquals($promo->id, $order->promo_id);
    }

    public function test_order_stores_customer_and_enforces_usage_per_customer(): void
    {
        $this->user = $this->makeUser();
        ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $this->product->id,
            'outlet_id' => $this->user->current_outlet_id,
            'quantity' => 10,
        ]);

        $customer = Customer::create([
            'name' => 'Siti',
            'email' => uniqid().'@cust.local',
            'is_active' => true,
        ]);

        $promo = Promo::create([
            'name' => 'MEMBERONLY'.uniqid(),
            'scope' => 'order',
            'type' => 'percentage',
            'discount_value' => 10,
            'usage_per_customer' => 1,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        // Isi keranjang dulu agar diskon promo dapat dihitung
        $this->actingAs($this->user)->postJson('/pos/cart/add', [
            'product_id' => $this->product->id,
        ])->assertOk();

        // Order pertama dengan customer: sukses + customer_id & promo_id tercatat
        $this->actingAs($this->user)->postJson('/pos/promo/apply', [
            'promo_code' => $promo->name,
            'customer_id' => $customer->id,
        ])->assertOk()->assertJson(['status' => 'success']);

        $first = $this->actingAs($this->user)->postJson('/pos/order', [
            'items' => [['id' => $this->product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'customer_id' => $customer->id,
            'customer_name' => 'Siti',
        ]);
        $first->assertOk();

        $order = Order::first();
        $this->assertEquals($customer->id, $order->customer_id);
        $this->assertEquals('Siti', $order->customer_name);
        $this->assertEquals($promo->id, $order->promo_id);

        // Pemakaian kedua oleh customer yang sama ditolak saat apply promo
        $secondApply = $this->postJson('/pos/promo/apply', [
            'promo_code' => $promo->name,
            'customer_id' => $customer->id,
        ]);
        $secondApply->assertOk()->assertJson(['status' => 'error']);

        // Final gate: simulasi race condition — session promo dipaksa ada
        // (misal dua tab apply bersamaan), proses order harus ditolak.
        $secondOrder = $this->actingAs($this->user)
            ->withSession([
                'pos_cart' => [
                    $this->product->id => [
                        'id' => $this->product->id,
                        'name' => $this->product->name,
                        'price' => 10000,
                        'quantity' => 1,
                        'subtotal' => 10000,
                    ],
                ],
                'pos_discount' => [
                    'promo_id' => $promo->id,
                    'code' => $promo->name,
                    'amount' => 1000,
                    'type' => 'percentage',
                    'scope' => 'order',
                    'eligible_product_ids' => [],
                ],
            ])
            ->postJson('/pos/order', [
                'items' => [['id' => $this->product->id, 'quantity' => 1]],
                'payment_method' => 'cash',
                'customer_id' => $customer->id,
            ]);
        $secondOrder->assertStatus(422);
    }
}
