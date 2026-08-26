<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminOrderStockTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Product $product;

    private ProductStock $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $outlet = Outlet::create([
            'name' => 'Admin Order Outlet '.uniqid(),
            'status' => true,
        ]);

        $category = ProductCategory::create([
            'name' => 'Cat '.uniqid(),
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Product '.uniqid(),
            'price' => 10000,
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Admin',
            'username' => 'admin_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $outlet->id,
        ]);
        $this->user->forceFill(['email_verified_at' => now()])->save();
        $this->user->outlets()->attach($outlet->id);

        // Beri role Super Admin agar lolos proteksi halaman admin
        $role = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['uuid' => Str::uuid()->toString()]
        );
        $this->user->assignRole($role);

        $this->stock = ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $this->product->id,
            'outlet_id' => $outlet->id,
            'quantity' => 10,
        ]);
    }

    public function test_store_decrements_stock_and_logs_movement(): void
    {
        $response = $this->actingAs($this->user)->post('/orders', [
            'payment_method' => 'cash',
            'status_order' => 'pending',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 3],
            ],
        ]);

        $response->assertRedirect(route('orders.index'));

        $this->stock->refresh();
        $this->assertEquals(7, $this->stock->quantity);

        $movement = StockMovement::where('product_stock_id', $this->stock->id)
            ->where('movement_type', 'out')
            ->where('notes', 'like', '%admin order%')
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(10, $movement->stock_before);
        $this->assertEquals(7, $movement->stock_after);
    }

    public function test_update_adjusts_stock_delta(): void
    {
        $this->actingAs($this->user)->post('/orders', [
            'payment_method' => 'cash',
            'status_order' => 'pending',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 3],
            ],
        ]);

        $order = Order::firstOrFail();

        // Ubah qty 3 -> 5: stok dikembalikan 3 lalu dipotong 5 (net -2)
        $response = $this->actingAs($this->user)->put("/orders/{$order->uuid}", [
            'payment_method' => 'cash',
            'status_order' => 'pending',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertRedirect(route('orders.index'));

        $this->stock->refresh();
        $this->assertEquals(5, $this->stock->quantity); // 10 - 3 + 3 - 5
    }

    public function test_destroy_restores_stock(): void
    {
        $this->actingAs($this->user)->post('/orders', [
            'payment_method' => 'cash',
            'status_order' => 'pending',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 4],
            ],
        ]);

        $order = Order::firstOrFail();

        $response = $this->actingAs($this->user)->delete("/orders/{$order->uuid}");
        $response->assertRedirect(route('orders.index'));

        $this->stock->refresh();
        $this->assertEquals(10, $this->stock->quantity);

        $returnMovement = StockMovement::where('product_stock_id', $this->stock->id)
            ->where('movement_type', 'return')
            ->first();
        $this->assertNotNull($returnMovement);
    }

    public function test_insufficient_stock_rejects_admin_order(): void
    {
        $response = $this->actingAs($this->user)->from(route('orders.create'))->post('/orders', [
            'payment_method' => 'cash',
            'status_order' => 'pending',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 99],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->stock->refresh();
        $this->assertEquals(10, $this->stock->quantity);
    }
}
