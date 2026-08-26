<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\Promo;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OutletScopingTest extends TestCase
{
    use DatabaseTransactions;

    private function makeStaffForOutlet(string $outletName): array
    {
        $outlet = Outlet::create([
            'name' => $outletName.' '.uniqid(),
            'status' => true,
        ]);

        $user = User::create([
            'name' => 'Staff '.$outletName,
            'username' => 'staff_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $outlet->id,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->outlets()->attach($outlet->id);

        return [$outlet, $user];
    }

    private function makeProduct(): Product
    {
        $category = ProductCategory::create([
            'name' => 'Cat '.uniqid(),
            'is_active' => true,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Scoped Product '.uniqid(),
            'price' => 5000,
            'is_active' => true,
        ]);
    }

    public function test_stock_movements_are_isolated_per_outlet(): void
    {
        [$outletA, $staffA] = $this->makeStaffForOutlet('Outlet A');
        [$outletB, $staffB] = $this->makeStaffForOutlet('Outlet B');

        $product = $this->makeProduct();

        $stockA = ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'outlet_id' => $outletA->id,
            'quantity' => 5,
        ]);

        $stockB = ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'outlet_id' => $outletB->id,
            'quantity' => 7,
        ]);

        // Masing-masing outlet punya 1 movement manual (in)
        foreach ([[$stockA, $staffA], [$stockB, $staffB]] as [$stock, $staff]) {
            $this->actingAs($staff)->post('/stock-movements', [
                'product_stock_id' => $stock->id,
                'movement_type' => 'in',
                'quantity' => 1,
                'notes' => 'restock test',
            ])->assertRedirect();

            // outlet_id harus terisi otomatis dari stok terkait
            $movement = StockMovement::where('product_stock_id', $stock->id)->first();
            $this->assertNotNull($movement);
            $this->assertEquals($stock->outlet_id, $movement->outlet_id);
        }

        // Staff A hanya melihat movement milik outlet A
        $this->actingAs($staffA);
        $visibleA = StockMovement::all();
        $this->assertEquals(1, $visibleA->count());
        $this->assertEquals($stockA->id, $visibleA->first()->product_stock_id);

        // DataTable endpoint juga terfilter untuk staff A
        $response = $this->actingAs($staffA)->get('/stock-movements');
        $response->assertOk();
    }

    public function test_promos_are_visible_to_owner_outlet_and_global(): void
    {
        [, $staffA] = $this->makeStaffForOutlet('Promo Outlet A');

        // Promo milik outlet LAIN (bukan outlet staffA) — dibuat langsung via DB
        $otherOutletId = Outlet::where('id', '!=', $staffA->current_outlet_id)->value('id');

        DB::table('promos')->insert([
            'uuid' => Str::uuid()->toString(),
            'name' => 'PROMO-OTHER-OUTLET'.uniqid(),
            'scope' => 'order',
            'type' => 'percentage',
            'discount_value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
            'outlet_id' => $otherOutletId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Promo GLOBAL (outlet_id NULL) — harus tetap terlihat
        DB::table('promos')->insert([
            'uuid' => Str::uuid()->toString(),
            'name' => 'PROMO-GLOBAL'.uniqid(),
            'scope' => 'order',
            'type' => 'percentage',
            'discount_value' => 5,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
            'outlet_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Staff A membuat promo → otomatis milik outlet-nya (butuh sesi login)
        $this->actingAs($staffA);

        $own = Promo::create([
            'name' => 'PROMO-MINE'.uniqid(),
            'scope' => 'order',
            'type' => 'percentage',
            'discount_value' => 15,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $this->assertNotNull($own->fresh()->outlet_id);
        $this->assertEquals($staffA->current_outlet_id, $own->fresh()->outlet_id);

        $names = Promo::pluck('name')->all();

        // Promo outlet sendiri + global terlihat; promo outlet lain TIDAK
        $this->assertContains($own->name, $names);

        $globalNames = collect($names)->filter(fn ($n) => str_starts_with($n, 'PROMO-GLOBAL'))->values();
        $this->assertCount(1, $globalNames);

        $otherNames = collect($names)->filter(fn ($n) => str_starts_with($n, 'PROMO-OTHER-OUTLET'));
        $this->assertCount(0, $otherNames);
    }

    public function test_order_type_is_persisted_and_defaults_to_dine_in(): void
    {
        [$outlet, $staff] = $this->makeStaffForOutlet('Type Outlet');
        $product = $this->makeProduct();

        ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'outlet_id' => $outlet->id,
            'quantity' => 10,
        ]);

        // Default dine_in saat tidak dikirim
        $this->actingAs($staff)->postJson('/pos/order', [
            'items' => [['id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
        ])->assertOk();

        $this->assertEquals('dine_in', Order::latest('id')->first()->order_type);

        // Takeaway tersimpan & table diabaikan
        $customer = Customer::create(['name' => 'Budi Takeaway', 'is_active' => true]);

        $this->actingAs($staff)->postJson('/pos/order', [
            'items' => [['id' => $product->id, 'quantity' => 2]],
            'payment_method' => 'cash',
            'order_type' => 'takeaway',
            'customer_id' => $customer->id,
            'table_id' => null,
        ])->assertOk();

        $takeaway = Order::latest('id')->first();
        $this->assertEquals('takeaway', $takeaway->order_type);
        $this->assertNull($takeaway->table_id);
        $this->assertEquals($customer->id, $takeaway->customer_id);
    }

    public function test_admin_panel_page_renders(): void
    {
        [, $staff] = $this->makeStaffForOutlet('Panel Outlet');

        $this->actingAs($staff)->get('/admin-panel')
            ->assertOk()
            ->assertSee('Admin Panel');
    }
}
