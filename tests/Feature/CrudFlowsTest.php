<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\Promo;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CrudFlowsTest extends TestCase
{
    use DatabaseTransactions;

    private function makeAdmin(): User
    {
        $outlet = Outlet::create([
            'name' => 'Crud Outlet '.uniqid(),
            'status' => true,
        ]);

        $user = User::create([
            'name' => 'Admin Crud',
            'username' => 'crud_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $outlet->id,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->outlets()->attach($outlet->id);

        $role = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['uuid' => Str::uuid()->toString()]
        );
        $user->assignRole($role);

        return $user;
    }

    public function test_customer_crud_flow(): void
    {
        $admin = $this->makeAdmin();

        // Create
        $this->actingAs($admin)->post('/customers', [
            'name' => 'Dewi Customer',
            'email' => uniqid().'@cust.local',
            'phone' => '081234567890',
            'is_active' => '1',
        ])->assertRedirect(route('customers.index'));

        $customer = Customer::where('name', 'Dewi Customer')->firstOrFail();
        $this->assertTrue($customer->is_active);

        // Update
        $this->actingAs($admin)->put("/customers/{$customer->uuid}", [
            'name' => 'Dewi Updated',
            'email' => $customer->email,
        ])->assertRedirect(route('customers.index'));

        $customer->refresh();
        $this->assertEquals('Dewi Updated', $customer->name);
        $this->assertFalse((bool) $customer->fresh()->is_active); // switch tidak dikirim

        // Delete (soft)
        $this->actingAs($admin)->delete("/customers/{$customer->uuid}")->assertRedirect();
        $this->assertSoftDeleted($customer);
    }

    public function test_promo_store_syncs_products_and_update_rescopes(): void
    {
        $admin = $this->makeAdmin();

        $category = ProductCategory::create(['name' => 'CrudCat '.uniqid(), 'is_active' => true]);
        $p1 = Product::create(['category_id' => $category->id, 'name' => 'P1 '.uniqid(), 'price' => 5000, 'is_active' => true]);
        $p2 = Product::create(['category_id' => $category->id, 'name' => 'P2 '.uniqid(), 'price' => 6000, 'is_active' => true]);

        // Store dengan scope product + sync products
        $this->actingAs($admin)->post('/promo', [
            'name' => 'CRUDPROMO'.uniqid(),
            'scope' => 'product',
            'type' => 'percentage',
            'discount_value' => '10',
            'start_date' => now()->subDay()->format('Y-m-d H:i'),
            'end_date' => now()->addDay()->format('Y-m-d H:i'),
            'products' => [$p1->id, $p2->id],
            'is_active' => '1',
        ])->assertRedirect(route('promo.index'));

        $promo = Promo::where('name', 'like', 'CRUDPROMO%')->firstOrFail();
        $this->assertEquals([$p1->id, $p2->id], $promo->products()->pluck('products.id')->all());

        // Update ganti scope ke category_product -> produk di-detach, kategori ter-sync
        $this->actingAs($admin)->put("/promo/{$promo->uuid}", [
            'name' => $promo->name,
            'scope' => 'category_product',
            'type' => 'percentage',
            'discount_value' => '10',
            'start_date' => now()->subDay()->format('Y-m-d H:i'),
            'end_date' => now()->addDay()->format('Y-m-d H:i'),
            'categories' => [$category->id],
        ])->assertRedirect(route('promo.index'));

        $promo->refresh();
        $this->assertCount(0, $promo->products);
        $this->assertEquals([$category->id], $promo->categories()->pluck('product_categories.id')->all());
    }

    public function test_product_stock_update_logs_adjustment_and_saves_price(): void
    {
        $admin = $this->makeAdmin();

        $category = ProductCategory::create(['name' => 'StockCat '.uniqid(), 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'SP '.uniqid(), 'price' => 8000, 'is_active' => true]);

        $stock = ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'outlet_id' => $admin->current_outlet_id,
            'quantity' => 5,
        ]);

        $this->actingAs($admin)
            ->from(route('product-stocks.edit', $stock->uuid))
            ->put("/product-stocks/{$stock->uuid}", [
                'quantity' => 12,
                'price' => '9.000',
            ])
            ->assertRedirect(route('product-stocks.index'));

        $stock->refresh();
        $this->assertEquals(12, $stock->quantity);
        $this->assertEquals(9000, (float) $stock->price);

        $movement = StockMovement::where('product_stock_id', $stock->id)
            ->where('movement_type', 'adjustment')
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(7, $movement->quantity); // selisih 5 -> 12
    }

    public function test_banned_user_cannot_login(): void
    {
        $outlet = Outlet::create(['name' => 'Ban Outlet '.uniqid(), 'status' => true]);

        $banned = User::create([
            'name' => 'Banned Guy',
            'username' => 'banned_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => Hash::make('password123'),
            'current_outlet_id' => $outlet->id,
        ]);
        $banned->forceFill(['email_verified_at' => now(), 'banned_at' => now()])->save();

        $response = $this->post('/login', [
            'email' => $banned->email,
            'password' => 'password123',
        ]);

        // Kredensial benar tapi diblokir: tidak boleh masuk & muncul error kredensial
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
