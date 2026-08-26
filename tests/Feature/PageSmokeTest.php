<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageSmokeTest extends TestCase
{
    use DatabaseTransactions;

    private function loginUser(): User
    {
        $outlet = Outlet::create([
            'name' => 'Smoke Outlet '.uniqid(),
            'status' => true,
        ]);

        $user = User::create([
            'name' => 'Smoke Tester',
            'username' => 'smoke_'.uniqid(),
            'email' => uniqid().'@test.local',
            'password' => bcrypt('password'),
            'current_outlet_id' => $outlet->id,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        // Super Admin agar lewati pembatasan outlet & permission di halaman admin
        $user->assignRole('Super Admin');
        $user->outlets()->attach($outlet->id);

        return $user;
    }

    public function test_key_pages_render(): void
    {
        $user = $this->loginUser();

        $routes = [
            'GET' => [
                '/dashboard',
                '/pos',
                '/kitchen',
                '/kitchen/history',
                '/orders',
                '/orders/create',
                '/products',
                '/products/create',
                '/product_categories',
                '/product_categories/create',
                '/product-stocks',
                '/product-stocks/create',
                '/stock-movements',
                '/stock-movements/create',
                '/promo',
                '/promo/create',
                '/tables',
                '/tables/create',
                '/customers',
                '/customers/create',
                '/acount',
                '/acount/security',
            ],
        ];

        foreach ($routes['GET'] as $url) {
            $response = $this->actingAs($user)->get($url);

            if (! $response->isOk()) {
                fwrite(STDERR, "SMOKE FAIL {$url}: HTTP ".$response->getStatusCode().PHP_EOL);
            }

            $response->assertOk();
        }
    }

    public function test_edit_pages_render(): void
    {
        $user = $this->loginUser();

        $category = ProductCategory::create([
            'name' => 'Smoke Cat '.uniqid(),
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Smoke Product '.uniqid(),
            'price' => 15000,
            'is_active' => true,
        ]);

        $stock = ProductStock::create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'outlet_id' => $user->current_outlet_id,
            'quantity' => 7,
            'price' => 13500,
        ]);

        $promo = Promo::create([
            'name' => 'SMOKEPROMO'.uniqid(),
            'scope' => 'category_product',
            'type' => 'percentage',
            'discount_value' => 5,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);
        $promo->categories()->sync([$category->id]);

        $table = DiningTable::create([
            'uuid' => Str::uuid()->toString(),
            'outlet_id' => $user->current_outlet_id,
            'number_table' => 'T'.uniqid(),
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Smoke Customer',
            'email' => uniqid().'@cust.local',
            'is_active' => true,
        ]);

        $order = Order::create([
            'uuid' => Str::uuid()->toString(),
            'code_invoice' => 'INV-SMOKE-'.strtoupper(Str::random(6)),
            'outlet_id' => $user->current_outlet_id,
            'cashier_id' => $user->id,
            'order_type' => 'dine_in',
            'payment_method' => 'cash',
            'status_order' => 'pending',
        ]);

        $urls = [
            '/promo/'.$promo->uuid.'/edit',
            '/product-stocks/'.$stock->uuid.'/edit',
            '/tables/'.$table->uuid.'/edit',
            '/customers/'.$customer->uuid.'/edit',
            '/kitchen/new-orders',
            '/kitchen/'.$order->uuid,
            '/kitchen/'.$order->uuid.'/print',
            '/orders/'.$order->uuid,
            '/admin-panel',
            '/transactions',
            '/transactions-report',
            '/transactions/'.$order->uuid,
            '/transactions/'.$order->uuid.'/receipt',
        ];

        foreach ($urls as $url) {
            $response = $this->actingAs($user)->get($url);

            if (! $response->isOk()) {
                fwrite(STDERR, "SMOKE EDIT FAIL {$url}: HTTP ".$response->getStatusCode().PHP_EOL);
            }

            $response->assertOk();
        }
    }

    public function test_remaining_module_pages_render(): void
    {
        $user = $this->loginUser();

        $menuGroup = MenuGroup::firstOrCreate(
            ['name' => 'SmokeMG'.substr(uniqid(), -8)],
            ['sort' => 99, 'status' => 1]
        );

        $menu = Menu::firstOrCreate(
            ['href' => '/smoke-menu-'.uniqid()],
            ['nama_menu' => 'Smoke Menu', 'status' => true, 'sort' => 99]
        );

        $permissionGroup = PermissionGroup::firstOrCreate(['name' => 'SmokePG'.substr(uniqid(), -8)]);

        $permission = Permission::firstOrCreate([
            'name' => 'Smoke Perm '.uniqid(),
            'guard_name' => 'web',
        ], ['permission_group_id' => $permissionGroup->id]);

        $role = Role::firstOrCreate(
            ['name' => 'Smoke Role '.uniqid(), 'guard_name' => 'web'],
            ['uuid' => Str::uuid()->toString()]
        );

        $category = ArticleCategory::create(['name' => 'Smoke ArtCat '.uniqid()]);
        $article = Article::create([
            'article_category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Smoke Article '.uniqid(),
            'image_path' => 'article-images/smoke.jpg',
            'excerpt' => 'smoke excerpt',
            'content' => '<p>body</p>',
            'published_at' => now(),
            'outlet_id' => $user->current_outlet_id,
        ]);

        $urls = [
            '/menu', '/menu/create', "/menu/{$menu->uuid}/edit",
            '/menugroup', '/menugroup/create', "/menugroup/{$menuGroup->uuid}/edit",
            '/permission', '/permission/create', "/permission/{$permission->uuid}/edit",
            '/permissiongroup', '/permissiongroup/create', "/permissiongroup/{$permissionGroup->uuid}/edit",
            '/role', '/role/create',
            '/user', '/user/create', "/user/{$user->uuid}/edit", "/user/role/{$user->uuid}",
            '/article', '/article/create', "/article/{$article->slug}/edit",
            '/outlet', '/outlet/create', '/acount/security',
        ];
        // role.show (matriks permission)
        $urls[] = "/role/{$role->uuid}";

        foreach ($urls as $url) {
            $response = $this->actingAs($user)->get($url);

            if (! $response->isOk()) {
                fwrite(STDERR, "SMOKE MODULE FAIL {$url}: HTTP ".$response->getStatusCode().PHP_EOL);
            }

            $response->assertOk();
        }
    }
}
