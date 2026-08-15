<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // ============================================================
        // 1. BUAT / AMBIL MENU GROUP (OTOMATIS)
        // ============================================================
        $produkGroup = MenuGroup::firstOrCreate(
            ['name' => 'Management Product'],
            ['permission_group_id' => 13, 'sort' => 1, 'status' => 1]
        );

        $mejaGroup = MenuGroup::firstOrCreate(
            ['name' => 'Management Table'],
            ['permission_group_id' => 15, 'sort' => 2, 'status' => 1]
        );

        $kontenGroup = MenuGroup::firstOrCreate(
            ['name' => 'Management Content'],
            ['permission_group_id' => 10, 'sort' => 3, 'status' => 1]
        );

        $pengaturanGroup = MenuGroup::firstOrCreate(
            ['name' => 'Setting'],
            ['permission_group_id' => 8, 'sort' => 4, 'status' => 1]
        );

        // ============================================================
        // 2. MANAGEMENT PRODUCT
        // ============================================================
        $produk = Menu::create([
            'menu_group_id' => $produkGroup->id,
            'nama_menu' => 'Produk',
            'permission_group_id' => 13,
            'icon' => 'ri-shopping-bag-3-line',
            'status' => true,
            'sort' => 1,
        ]);

        $promo = Menu::create([
            'menu_group_id' => $produkGroup->id,
            'nama_menu' => 'Promo',
            'permission_group_id' => 14,
            'icon' => 'ri-gift-line',
            'status' => true,
            'sort' => 2,
        ]);

        // Stock Management (HANYA SATU, TIDAK DUPLIKAT)
        $stockManagement = Menu::create([
            'menu_group_id' => $produkGroup->id,
            'nama_menu' => 'Stock Management',
            'permission_group_id' => 16,
            'icon' => 'ri-stack-line',
            'status' => true,
            'sort' => 3,
        ]);

        $customerPromo = Menu::create([
            'menu_group_id' => $produkGroup->id,
            'nama_menu' => 'Customer Promo',
            'permission_group_id' => 26,
            'icon' => 'ri-gift-line',
            'status' => true,
            'sort' => 4,
        ]);

        // Submenu Produk
        Menu::create([
            'menu_id' => $produk->id,
            'nama_menu' => 'Kategori Produk',
            'permission_group_id' => 12,
            'href' => '/product_categories',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'menu_id' => $produk->id,
            'nama_menu' => 'Produk',
            'permission_group_id' => 13,
            'href' => '/products',
            'status' => true,
            'sort' => 2,
        ]);

        // Submenu Promo
        Menu::create([
            'menu_id' => $promo->id,
            'nama_menu' => 'Promo',
            'permission_group_id' => 14,
            'href' => '/promo',
            'status' => true,
            'sort' => 1,
        ]);

        // Submenu Stock Management
        Menu::create([
            'menu_id' => $stockManagement->id,
            'nama_menu' => 'Product Stocks',
            'permission_group_id' => 16,
            'href' => '/product-stocks',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'menu_id' => $stockManagement->id,
            'nama_menu' => 'Stock Movements',
            'permission_group_id' => 17,
            'href' => '/stock-movements',
            'status' => true,
            'sort' => 2,
        ]);

        // Submenu Customer Promo
        Menu::create([
            'menu_id' => $customerPromo->id,
            'nama_menu' => 'Customer Promo',
            'permission_group_id' => 26,
            'href' => '/customer-promos',
            'status' => true,
            'sort' => 1,
        ]);

        // ============================================================
        // 3. MANAGEMENT TABLE
        // ============================================================
        Menu::create([
            'menu_group_id' => $mejaGroup->id,
            'nama_menu' => 'Management Meja',
            'permission_group_id' => 15,
            'icon' => 'ri-layout-grid-line',
            'href' => '/tables',
            'status' => true,
            'sort' => 1,
        ]);

        // ============================================================
        // 4. MANAGEMENT CONTENT (Artikel)
        // ============================================================
        $artikel = Menu::create([
            'menu_group_id' => $kontenGroup->id,
            'nama_menu' => 'Artikel',
            'permission_group_id' => 7,
            'icon' => 'ri-article-line',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'menu_id' => $artikel->id,
            'nama_menu' => 'Artikel Kategori',
            'permission_group_id' => 7,
            'href' => '/article_categories',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'menu_id' => $artikel->id,
            'nama_menu' => 'Artikel',
            'permission_group_id' => 7,
            'href' => '/article',
            'status' => true,
            'sort' => 2,
        ]);

        // ============================================================
        // 5. SETTING
        // ============================================================
        $setting = Menu::create([
            'menu_group_id' => $pengaturanGroup->id,
            'nama_menu' => 'Setting',
            'permission_group_id' => 8,
            'icon' => 'ri-settings-3-line',
            'status' => true,
            'sort' => 1,
        ]);

        $userManagement = Menu::create([
            'menu_id' => $setting->id,
            'nama_menu' => 'User Management',
            'permission_group_id' => 8,
            'status' => true,
            'sort' => 1,
        ]);

        // Level 3 dari User Management
        Menu::create([
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Users',
            'permission_group_id' => 1,
            'href' => '/user',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Permission Group',
            'permission_group_id' => 8,
            'href' => '/permissiongroup',
            'status' => true,
            'sort' => 2,
        ]);

        Menu::create([
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Permissions',
            'permission_group_id' => 8,
            'href' => '/permission',
            'status' => true,
            'sort' => 3,
        ]);

        Menu::create([
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Roles',
            'permission_group_id' => 8,
            'href' => '/role',
            'status' => true,
            'sort' => 4,
        ]);

        Menu::create([
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Outlets',
            'permission_group_id' => 9,
            'href' => '/outlet',
            'status' => true,
            'sort' => 5,
        ]);

        // Submenu Web Setting (langsung di bawah Setting)
        Menu::create([
            'menu_id' => $setting->id,
            'nama_menu' => 'Web Setting',
            'permission_group_id' => 8,
            'href' => '/setting',
            'status' => true,
            'sort' => 2,
        ]);

        Menu::create([
            'menu_id' => $setting->id,
            'nama_menu' => 'Menu Management',
            'permission_group_id' => 8,
            'href' => '/menu',
            'status' => true,
            'sort' => 3,
        ]);

        Menu::create([
            'menu_id' => $setting->id,
            'nama_menu' => 'Menu Group',
            'permission_group_id' => 8,
            'href' => '/menugroup',
            'status' => true,
            'sort' => 4,
        ]);

        // ============================================================
        // 6. ORDERS (LANGSUNG KE HALAMAN - TANPA DROPDOWN)
        // ============================================================
        Menu::create([
            'uuid' => Str::uuid(),
            'menu_group_id' => null,
            'menu_id' => null,
            'nama_menu' => 'Orders',
            'icon' => 'ri-shopping-cart-line',
            'permission_group_id' => 18,
            'href' => '/orders',
            'status' => true,
            'sort' => 1,
        ]);

        // ============================================================
        // 7. POS (LANGSUNG KE HALAMAN)
        // ============================================================
        Menu::create([
            'uuid' => Str::uuid(),
            'menu_group_id' => null,
            'menu_id' => null,
            'nama_menu' => 'POS',
            'icon' => 'ri-shopping-cart-2-line',
            'permission_group_id' => 20,
            'href' => '/pos',
            'status' => true,
            'sort' => 1,
        ]);

        // ============================================================
        // 8. CUSTOMERS (LANGSUNG KE HALAMAN)
        // ============================================================
        Menu::create([
            'uuid' => Str::uuid(),
            'menu_group_id' => null,
            'menu_id' => null,
            'nama_menu' => 'Customers',
            'icon' => 'ri-user-line',
            'permission_group_id' => 21,
            'href' => '/customers',
            'status' => true,
            'sort' => 1,
        ]);

        // ============================================================
        // 9. KITCHEN (LANGSUNG KE HALAMAN)
        // ============================================================
        Menu::create([
            'uuid' => Str::uuid(),
            'menu_group_id' => null,
            'menu_id' => null,
            'nama_menu' => 'Kitchen',
            'icon' => 'ri-restaurant-2-line',
            'permission_group_id' => 22,
            'href' => '/kitchen',
            'status' => true,
            'sort' => 1,
        ]);
    }
}
