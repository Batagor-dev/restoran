<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private User $plain;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $outlet = Outlet::create([
            'name' => 'Report Outlet '.uniqid(),
            'status' => true,
        ]);

        $category = ProductCategory::create([
            'name' => 'ReportCat '.uniqid(),
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Report Product '.uniqid(),
            'price' => 10000,
            'cost_price' => 6000,
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Report Admin',
            'username' => 'rep_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $outlet->id,
        ]);
        $this->admin->forceFill(['email_verified_at' => now()])->save();
        $this->admin->outlets()->attach($outlet->id);

        $role = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['uuid' => Str::uuid()->toString()]
        );
        $this->admin->assignRole($role);

        // User TANPA permission Report
        $this->plain = User::create([
            'name' => 'No Rep Perm',
            'username' => 'norep_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $outlet->id,
        ]);
        $this->plain->forceFill(['email_verified_at' => now()])->save();
        $this->plain->outlets()->attach($outlet->id);

        ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $this->product->id,
            'outlet_id' => $outlet->id,
            'quantity' => 3,
        ]);
    }

    private function createPosOrder(int $qty): Order
    {
        $this->actingAs($this->admin)->postJson('/pos/order', [
            'items' => [['id' => $this->product->id, 'quantity' => $qty]],
            'payment_method' => 'cash',
        ])->assertOk();

        return Order::latest('id')->first();
    }

    public function test_reports_hub_renders_and_gates(): void
    {
        $this->actingAs($this->admin)->get('/reports')
            ->assertOk()
            ->assertSee('Sales Report');

        $this->actingAs($this->plain)->get('/reports')->assertStatus(403);
    }

    public function test_all_report_types_render_for_admin(): void
    {
        $types = array_keys(ReportController::registry());
        $this->assertCount(15, $types);

        foreach ($types as $type) {
            $response = $this->actingAs($this->admin)->get('/reports/'.$type);

            if (! $response->isOk()) {
                fwrite(STDERR, "REPORT FAIL {$type}: HTTP ".$response->getStatusCode().PHP_EOL);
            }

            $response->assertOk();
        }

        // Tipe tak dikenal -> 404
        $this->actingAs($this->admin)->get('/reports/hackerman')->assertStatus(404);

        // Tanpa permission -> 403
        $this->actingAs($this->plain)->get('/reports/sales')->assertStatus(403);
    }

    public function test_product_sales_aggregation_is_correct(): void
    {
        $order = $this->createPosOrder(2); // 2 x 10000 = subtotal 20000

        $content = $this->actingAs($this->admin)
            ->get('/reports/product-sales?start_date='.now()->subDay()->format('Y-m-d'))
            ->getContent();

        $this->assertStringContainsString($this->product->name, $content);
        $this->assertStringContainsString(number_format(20000, 0, ',', '.'), $content);
    }

    public function test_profit_report_uses_cost_price(): void
    {
        $this->createPosOrder(2); // revenue 20000, cost 12000, profit 8000

        $content = $this->actingAs($this->admin)
            ->get('/reports/profit')
            ->getContent();

        $this->assertStringContainsString('Gross Profit', $content);
        $this->assertStringContainsString(number_format(8000, 0, ',', '.'), $content);
    }

    public function test_low_stock_lists_products_below_threshold(): void
    {
        // Stok tersisa 3 (dari setUp), threshold default 5 -> harus muncul
        $content = $this->actingAs($this->admin)->get('/reports/low-stock')->getContent();
        $this->assertStringContainsString($this->product->name, $content);

        // Threshold 1 -> tidak muncul
        $content = $this->actingAs($this->admin)->get('/reports/low-stock?threshold=1')->getContent();
        $this->assertStringNotContainsString($this->product->name, $content);
    }

    public function test_refund_appears_in_refund_report(): void
    {
        $order = $this->createPosOrder(1);
        $reason = 'barang rusak';

        $this->actingAs($this->admin)->post('/transactions/'.$order->uuid.'/refund', [
            'reason' => $reason,
        ])->assertRedirect();

        $content = $this->actingAs($this->admin)
            ->get('/reports/refund?start_date='.now()->subDay()->format('Y-m-d'))
            ->getContent();

        $this->assertStringContainsString($order->code_invoice, $content);
        $this->assertStringContainsString($reason, $content);
    }

    public function test_export_csv_downloads_with_headers(): void
    {
        $this->createPosOrder(1);

        $response = $this->actingAs($this->admin)->get('/reports/export/product-sales');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        // fputcsv dapat mengutip kolom; cukup pastikan header & data ada
        $this->assertStringContainsString('Product', $content);
        $this->assertStringContainsString('Qty Sold', $content);
        $this->assertStringContainsString($this->product->name, $content);
    }
}
