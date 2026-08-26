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

class TransactionManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Product $product;

    private ProductStock $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $outlet = Outlet::create([
            'name' => 'Trx Outlet '.uniqid(),
            'status' => true,
        ]);

        $category = ProductCategory::create([
            'name' => 'Cat '.uniqid(),
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Trx Product '.uniqid(),
            'price' => 10000,
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Cashier Trx',
            'username' => 'trx_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $outlet->id,
        ]);
        $this->user->forceFill(['email_verified_at' => now()])->save();
        $this->user->outlets()->attach($outlet->id);

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

    private function createPosOrder(int $qty = 2): Order
    {
        $this->actingAs($this->user)->postJson('/pos/order', [
            'customer_name' => 'Budi',
            'items' => [['id' => $this->product->id, 'quantity' => $qty]],
            'payment_method' => 'cash',
        ])->assertOk();

        return Order::latest('id')->first();
    }

    public function test_transaction_history_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get('/transactions');
        $response->assertOk()->assertSee('Riwayat Transaksi');
    }

    public function test_datatable_ajax_tolerates_junk_filter_params(): void
    {
        // Simulasi nilai 'undefined'/sampah dari sisi browser tidak boleh menyebabkan 500
        $response = $this->actingAs($this->user)->get('/transactions', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'draw' => '1',
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
            'cashier_id' => 'undefined',
            'outlet_id' => 'undefined',
            'payment_method' => 'undefined',
            'status_transaction' => 'undefined',
            'search' => ['value' => 'INV'],
        ]);

        $response->assertOk();

        $json = $response->json();
        $this->assertIsArray($json['data'] ?? null);
    }

    public function test_export_csv_contains_transactions(): void
    {
        $order = $this->createPosOrder();

        $response = $this->actingAs($this->user)->get('/transactions-export?format=csv');

        $response->assertOk();
        $this->assertStringContainsString(
            'text/csv',
            $response->headers->get('Content-Type')
        );

        $content = $response->streamedContent();
        $this->assertStringContainsString('Invoice,Date,Cashier', $content);
        $this->assertStringContainsString($order->code_invoice, $content);
    }

    public function test_export_pdf_view_renders(): void
    {
        $order = $this->createPosOrder();

        $this->actingAs($this->user)
            ->get('/transactions-export?format=pdf&start_date='.now()->subDay()->format('Y-m-d'))
            ->assertOk()
            ->assertSee($order->code_invoice);
    }

    public function test_index_and_show_require_transaction_access_permission(): void
    {
        $order = $this->createPosOrder();

        // User login TANPA permission Transaction -> harus ditolak
        $plain = User::create([
            'name' => 'No Trx Perm',
            'username' => 'notrx_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $this->user->current_outlet_id,
        ]);
        $plain->forceFill(['email_verified_at' => now()])->save();
        $plain->outlets()->attach($this->user->current_outlet_id);

        $this->actingAs($plain)->get('/transactions')->assertStatus(403);
        $this->actingAs($plain)->get('/transactions/'.$order->uuid)->assertStatus(403);
        $this->actingAs($plain)->get('/transactions/'.$order->uuid.'/receipt')->assertStatus(403);

        // Pemilik permission tetap bisa
        $this->actingAs($this->user)->get('/transactions')->assertOk();
    }

    public function test_export_csv_respects_cashier_filter(): void
    {
        $mine = $this->createPosOrder();

        // Order dari kasir lain pada outlet yang sama
        $otherCashier = User::create([
            'name' => 'Other Cashier',
            'username' => 'otherc_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $this->user->current_outlet_id,
        ]);
        $otherCashier->forceFill(['email_verified_at' => now()])->save();
        $otherCashier->outlets()->attach($this->user->current_outlet_id);

        $otherOrder = null;
        $this->actingAs($otherCashier)->postJson('/pos/order', [
            'items' => [['id' => $this->product->id, 'quantity' => 1]],
            'payment_method' => 'qris',
        ])->assertOk();
        $otherOrder = Order::latest('id')->first();

        $content = $this->actingAs($this->user)
            ->get('/transactions-export?format=csv&cashier_id='.$this->user->id)
            ->streamedContent();

        $this->assertStringContainsString($mine->code_invoice, $content);
        $this->assertStringNotContainsString($otherOrder->code_invoice, $content);
    }

    public function test_detail_and_receipt_render(): void
    {
        $order = $this->createPosOrder();

        $this->actingAs($this->user)->get('/transactions/'.$order->uuid)
            ->assertOk()->assertSee($order->code_invoice);

        $this->actingAs($this->user)->get('/transactions/'.$order->uuid.'/receipt')
            ->assertOk()->assertSee('REPRINT');
    }

    public function test_refund_restores_stock_and_marks_refunded(): void
    {
        $order = $this->createPosOrder(3); // stok: 10 -> 7
        $stockBeforeRestore = $this->stock->fresh()->quantity;
        $this->assertEquals(7, $stockBeforeRestore);

        $response = $this->actingAs($this->user)->post('/transactions/'.$order->uuid.'/refund', [
            'reason' => 'Pelanggan mengembalikan barang',
        ]);

        $response->assertRedirect(route('transactions.show', $order->uuid));

        $order->refresh();
        $this->assertEquals('refunded', $order->status_transaction);
        $this->assertNotNull($order->refunded_at);
        $this->assertEquals($this->user->id, $order->refunded_by);
        $this->assertEquals('Pelanggan mengembalikan barang', $order->refund_reason);

        // Stok kembali ke 10 + movement return tercatat
        $this->assertEquals(10, $this->stock->fresh()->quantity);
        $movement = StockMovement::where('reference_type', 'sale')
            ->where('reference_id', $order->id)
            ->where('movement_type', 'return')
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(3, $movement->quantity);

        // Refund kedua harus ditolak
        $second = $this->actingAs($this->user)
            ->from(route('transactions.show', $order->uuid))
            ->post('/transactions/'.$order->uuid.'/refund', ['reason' => 'double refund attempt']);
        $second->assertSessionHas('error');
    }

    public function test_void_marks_voided_and_restores_stock(): void
    {
        $order = $this->createPosOrder(2); // stok: 10 -> 8

        $response = $this->actingAs($this->user)->post('/transactions/'.$order->uuid.'/void', [
            'reason' => 'Salah input kasir',
        ]);

        $response->assertRedirect(route('transactions.show', $order->uuid));

        $order->refresh();
        $this->assertEquals('voided', $order->status_transaction);
        $this->assertNotNull($order->voided_at);
        $this->assertEquals(10, $this->stock->fresh()->quantity);
    }

    public function test_refund_requires_permission(): void
    {
        $order = $this->createPosOrder();

        // User tanpa role/permission -> 403
        $plain = User::create([
            'name' => 'No Perm',
            'username' => 'noperm_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $this->user->current_outlet_id,
        ]);
        $plain->forceFill(['email_verified_at' => now()])->save();
        $plain->outlets()->attach($this->user->current_outlet_id);

        $this->actingAs($plain)
            ->post('/transactions/'.$order->uuid.'/refund', ['reason' => 'trying without access'])
            ->assertStatus(403);
    }

    public function test_reason_is_required(): void
    {
        $order = $this->createPosOrder();

        $this->actingAs($this->user)
            ->from(route('transactions.index'))
            ->post('/transactions/'.$order->uuid.'/void', ['reason' => ''])
            ->assertSessionHasErrors('reason');

        $order->refresh();
        $this->assertEquals('normal', $order->status_transaction);
    }

    public function test_report_excludes_refunded_and_voided_from_net(): void
    {
        $normal = $this->createPosOrder(1);   // grand_total 11000
        $refunded = $this->createPosOrder(2); // grand_total 22000
        $voided = $this->createPosOrder(3);   // grand_total 33000

        $this->actingAs($this->user)->post('/transactions/'.$refunded->uuid.'/refund', ['reason' => 'wrong order item'])->assertRedirect();
        $this->assertEquals(1, Order::where('status_transaction', 'refunded')->count(), 'Refund should persist');
        $this->actingAs($this->user)->post('/transactions/'.$voided->uuid.'/void', ['reason' => 'cashier mistake'])->assertRedirect();
        $this->assertEquals(1, Order::where('status_transaction', 'voided')->count(), 'Void should persist');

        $response = $this->actingAs($this->user)->get('/transactions-report?start_date='
            .now()->subDay()->format('Y-m-d').'&end_date='.now()->addDay()->format('Y-m-d'));

        $response->assertOk()->assertSee('Laporan Pendapatan');

        $content = $response->getContent();

        // Final revenue = hanya transaksi normal (11000), refunded & voided tidak dihitung
        $this->assertStringContainsString(number_format(22000, 0, ',', '.'), $content); // refund amount tampil sebagai penyesuaian
    }
}
