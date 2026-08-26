<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    /**
     * Ambil / buat menu secara aman:
     * - cari termasuk soft-deleted -> restore (JANGAN buat duplikat)
     * - kunci pencarian: href untuk leaf, nama_menu+menu_group_id untuk parent
     * - selalu relink permission_group_id berdasarkan NAMA grup permission
     *   (tahan terhadap pergeseran id di database).
     */
    private function ensureMenu(array $find, array $data): Menu
    {
        $permissionGroup = ! empty($data['permission_group'])
            ? PermissionGroup::firstOrCreate(['name' => $data['permission_group']])
            : null;

        $menu = Menu::withTrashed()
            ->where(function ($q) use ($find) {
                if (! empty($find['href'])) {
                    $q->where('href', $find['href']);
                } else {
                    $q->where('nama_menu', $find['nama_menu'])
                        ->where('menu_group_id', $find['menu_group_id'] ?? null)
                        ->whereNull('href');
                }
            })
            ->first();

        if (! $menu) {
            $menu = new Menu;
            $menu->uuid = Str::uuid()->toString();
        }

        $menu->nama_menu = $data['nama_menu'];
        $menu->menu_group_id = $data['menu_group_id'] ?? ($menu->menu_group_id ?? null);
        $menu->menu_id = $data['menu_id'] ?? ($menu->menu_id ?? null);
        $menu->href = $data['href'] ?? ($menu->href ?? null);
        $menu->icon = $data['icon'] ?? ($menu->icon ?? null);
        $menu->permission_group_id = $permissionGroup?->id ?? ($menu->permission_group_id ?? null);
        $menu->status = $data['status'] ?? true;
        $menu->sort = $data['sort'] ?? ($menu->sort ?? 0);

        if ($menu->trashed()) {
            $menu->restore();
        }

        $menu->save();

        return $menu;
    }

    private function ensureGroup(string $name, string $permissionGroupName, int $sort): MenuGroup
    {
        $group = MenuGroup::firstOrCreate(
            ['name' => $name],
            [
                'permission_group_id' => PermissionGroup::firstOrCreate(['name' => $permissionGroupName])->id,
                'sort' => $sort,
                'status' => 1,
            ]
        );

        // Self-healing: pastikan relasi ke permission group valid
        if (! $group->permission_group_id || ! PermissionGroup::find($group->permission_group_id)) {
            $group->update([
                'permission_group_id' => PermissionGroup::firstOrCreate(['name' => $permissionGroupName])->id,
            ]);
        }

        return $group;
    }

    public function run()
    {
        // ============================================================
        // 1. MENU GROUPS
        // ============================================================
        $produkGroup = $this->ensureGroup('Management Product', 'Product', 1);
        $mejaGroup = $this->ensureGroup('Management Table', 'Table', 2);
        $kontenGroup = $this->ensureGroup('Management Content', 'Konten', 3);
        $pengaturanGroup = $this->ensureGroup('Setting', 'Setting', 4);
        $reportGroup = $this->ensureGroup('Management Report', 'Order', 6);

        // ============================================================
        // 2. MANAGEMENT PRODUCT
        // ============================================================
        $produk = $this->ensureMenu(
            ['nama_menu' => 'Produk', 'menu_group_id' => $produkGroup->id],
            ['nama_menu' => 'Produk', 'menu_group_id' => $produkGroup->id, 'icon' => 'ri-shopping-bag-3-line', 'status' => true, 'sort' => 1]
        );

        $promo = $this->ensureMenu(
            ['nama_menu' => 'Promo', 'menu_group_id' => $produkGroup->id],
            ['nama_menu' => 'Promo', 'menu_group_id' => $produkGroup->id, 'icon' => 'ri-gift-line', 'status' => true, 'sort' => 2]
        );

        $stockManagement = $this->ensureMenu(
            ['nama_menu' => 'Stock Management', 'menu_group_id' => $produkGroup->id],
            ['nama_menu' => 'Stock Management', 'menu_group_id' => $produkGroup->id, 'icon' => 'ri-stack-line', 'status' => true, 'sort' => 3]
        );

        $customerPromo = $this->ensureMenu(
            ['nama_menu' => 'Customer Promo', 'menu_group_id' => $produkGroup->id],
            ['nama_menu' => 'Customer Promo', 'menu_group_id' => $produkGroup->id, 'icon' => 'ri-gift-line', 'status' => true, 'sort' => 4]
        );

        $this->ensureMenu(
            ['href' => '/product_categories'],
            ['nama_menu' => 'Kategori Produk', 'menu_id' => $produk->id, 'href' => '/product_categories', 'permission_group' => 'Product Category', 'status' => true, 'sort' => 1]
        );
        $this->ensureMenu(
            ['href' => '/products'],
            ['nama_menu' => 'Produk', 'menu_id' => $produk->id, 'href' => '/products', 'permission_group' => 'Product', 'status' => true, 'sort' => 2]
        );
        $this->ensureMenu(
            ['href' => '/promo'],
            ['nama_menu' => 'Promo', 'menu_id' => $promo->id, 'href' => '/promo', 'permission_group' => 'Promo', 'status' => true, 'sort' => 1]
        );
        $this->ensureMenu(
            ['href' => '/product-stocks'],
            ['nama_menu' => 'Product Stocks', 'menu_id' => $stockManagement->id, 'href' => '/product-stocks', 'permission_group' => 'Product Stock', 'status' => true, 'sort' => 1]
        );
        $this->ensureMenu(
            ['href' => '/stock-movements'],
            ['nama_menu' => 'Stock Movements', 'menu_id' => $stockManagement->id, 'href' => '/stock-movements', 'permission_group' => 'Stock Movement', 'status' => true, 'sort' => 2]
        );
        $this->ensureMenu(
            ['href' => '/customer-promos'],
            ['nama_menu' => 'Customer Promo', 'menu_id' => $customerPromo->id, 'href' => '/customer-promos', 'permission_group' => 'Customer Promo', 'status' => true, 'sort' => 1]
        );

        // ============================================================
        // 3. MANAGEMENT TABLE
        // ============================================================
        $this->ensureMenu(
            ['href' => '/tables'],
            ['nama_menu' => 'Management Meja', 'menu_group_id' => $mejaGroup->id, 'href' => '/tables', 'icon' => 'ri-layout-grid-line', 'permission_group' => 'Table', 'status' => true, 'sort' => 1]
        );

        // ============================================================
        // 4. MANAGEMENT CONTENT
        // ============================================================
        $artikel = $this->ensureMenu(
            ['nama_menu' => 'Artikel', 'menu_group_id' => $kontenGroup->id],
            ['nama_menu' => 'Artikel', 'menu_group_id' => $kontenGroup->id, 'icon' => 'ri-article-line', 'status' => true, 'sort' => 1]
        );

        $this->ensureMenu(
            ['href' => '/article_categories'],
            ['nama_menu' => 'Artikel Kategori', 'menu_id' => $artikel->id, 'href' => '/article_categories', 'permission_group' => 'Article Category', 'status' => true, 'sort' => 1]
        );
        $this->ensureMenu(
            ['href' => '/article'],
            ['nama_menu' => 'Artikel', 'menu_id' => $artikel->id, 'href' => '/article', 'permission_group' => 'Article', 'status' => true, 'sort' => 2]
        );

        // ============================================================
        // 5. SETTING
        // ============================================================
        $setting = $this->ensureMenu(
            ['nama_menu' => 'Setting', 'menu_group_id' => $pengaturanGroup->id],
            ['nama_menu' => 'Setting', 'menu_group_id' => $pengaturanGroup->id, 'icon' => 'ri-settings-3-line', 'status' => true, 'sort' => 1]
        );

        $userManagement = $this->ensureMenu(
            ['nama_menu' => 'User Management', 'menu_id' => $setting->id],
            ['nama_menu' => 'User Management', 'menu_id' => $setting->id, 'status' => true, 'sort' => 1]
        );

        $this->ensureMenu(
            ['href' => '/user'],
            ['nama_menu' => 'Users', 'menu_id' => $userManagement->id, 'href' => '/user', 'permission_group' => 'User', 'status' => true, 'sort' => 1]
        );
        $this->ensureMenu(
            ['href' => '/permissiongroup'],
            ['nama_menu' => 'Permission Group', 'menu_id' => $userManagement->id, 'href' => '/permissiongroup', 'permission_group' => 'Setting', 'status' => true, 'sort' => 2]
        );
        $this->ensureMenu(
            ['href' => '/permission'],
            ['nama_menu' => 'Permissions', 'menu_id' => $userManagement->id, 'href' => '/permission', 'permission_group' => 'Setting', 'status' => true, 'sort' => 3]
        );
        $this->ensureMenu(
            ['href' => '/role'],
            ['nama_menu' => 'Roles', 'menu_id' => $userManagement->id, 'href' => '/role', 'permission_group' => 'Setting', 'status' => true, 'sort' => 4]
        );
        $this->ensureMenu(
            ['href' => '/outlet'],
            ['nama_menu' => 'Outlets', 'menu_id' => $userManagement->id, 'href' => '/outlet', 'permission_group' => 'Outlet', 'status' => true, 'sort' => 5]
        );
        $this->ensureMenu(
            ['href' => '/setting'],
            ['nama_menu' => 'Web Setting', 'menu_id' => $setting->id, 'href' => '/setting', 'permission_group' => 'Setting', 'status' => true, 'sort' => 2]
        );
        $this->ensureMenu(
            ['href' => '/menu'],
            ['nama_menu' => 'Menu Management', 'menu_id' => $setting->id, 'href' => '/menu', 'permission_group' => 'Setting', 'status' => true, 'sort' => 3]
        );
        $this->ensureMenu(
            ['href' => '/menugroup'],
            ['nama_menu' => 'Menu Group', 'menu_id' => $setting->id, 'href' => '/menugroup', 'permission_group' => 'Setting', 'status' => true, 'sort' => 4]
        );

        // ============================================================
        // 6. REPORT GROUP
        // ============================================================
        $this->ensureMenu(
            ['href' => '/orders'],
            ['nama_menu' => 'Orders', 'menu_group_id' => $reportGroup->id, 'href' => '/orders', 'icon' => 'ri-shopping-cart-line', 'permission_group' => 'Order', 'status' => true, 'sort' => 1]
        );
        $this->ensureMenu(
            ['href' => '/transactions'],
            ['nama_menu' => 'Transactions', 'menu_group_id' => $reportGroup->id, 'href' => '/transactions', 'icon' => 'ri-receipt-line', 'permission_group' => 'Transaction', 'status' => true, 'sort' => 2]
        );
        $this->ensureMenu(
            ['href' => '/reports'],
            ['nama_menu' => 'Reports', 'menu_group_id' => $reportGroup->id, 'href' => '/reports', 'icon' => 'ri-bar-chart-grouped-line', 'permission_group' => 'Report', 'status' => true, 'sort' => 3]
        );

        // ============================================================
        // 7. STANDALONE
        // ============================================================
        $this->ensureMenu(
            ['href' => '/pos'],
            ['nama_menu' => 'POS', 'href' => '/pos', 'icon' => 'ri-shopping-cart-2-line', 'permission_group' => 'POS', 'status' => true, 'sort' => 1]
        );
        $this->ensureMenu(
            ['href' => '/kitchen'],
            ['nama_menu' => 'Kitchen', 'href' => '/kitchen', 'icon' => 'ri-restaurant-2-line', 'permission_group' => 'Kitchen', 'status' => true, 'sort' => 1]
        );

        // ============================================================
        // 8. MENU TAMBAHAN LAINNYA (IDEMPOTENT)
        // ============================================================
        $this->seedCustomerMenu($produkGroup);
        $this->seedAdminPanelMenu($pengaturanGroup);

        // Bersihkan duplikasi historis: sisakan satu baris aktif per identitas
        $this->removeDuplicates();
    }

    private function seedCustomerMenu(MenuGroup $produkGroup): void
    {
        $this->ensureMenu(
            ['href' => '/customers'],
            ['nama_menu' => 'Customers', 'menu_group_id' => $produkGroup->id, 'href' => '/customers', 'icon' => 'ri-user-smile-line', 'permission_group' => 'Customer', 'status' => true, 'sort' => 5]
        );
    }

    private function seedAdminPanelMenu(MenuGroup $pengaturanGroup): void
    {
        $this->ensureMenu(
            ['href' => '/admin-panel'],
            ['nama_menu' => 'Admin Panel', 'menu_group_id' => $pengaturanGroup->id, 'href' => '/admin-panel', 'icon' => 'ri-dashboard-3-line', 'permission_group' => 'Setting', 'status' => true, 'sort' => 0]
        );
    }

    /**
     * Hapus duplikasi historis akibat seeder lama tanpa guard:
     * sisakan SATU baris aktif per identitas (href untuk leaf,
     * nama_menu+menu_group_id untuk parent). Sisanya force-delete.
     */
    private function removeDuplicates(): void
    {
        $identities = [];

        foreach (Menu::withTrashed()->orderBy('id')->get() as $menu) {
            $key = ! empty($menu->href)
                ? 'h:'.$menu->href
                : 'n:'.$menu->nama_menu.'|g:'.$menu->menu_group_id.'|p:'.$menu->menu_id;

            if (! isset($identities[$key])) {
                $identities[$key] = $menu;

                continue;
            }

            $keeper = $identities[$key];

            // Repoint anak-anak ke baris yang dipertahankan
            Menu::where('menu_id', $menu->id)->update(['menu_id' => $keeper->id]);

            $menu->forceDelete();
        }
    }
}
