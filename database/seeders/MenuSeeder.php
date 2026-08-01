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
        $produkGroup = MenuGroup::where('name', 'Management Product')->first();
        $mejaGroup = MenuGroup::where('name', 'Management Table')->first();
        $kontenGroup = MenuGroup::where('name', 'Management Content')->first();
        $pengaturanGroup = MenuGroup::where('name', 'Setting')->first();

        // === Menu 1: Produk (Group: PRODUK) ===
        $produk = Menu::create([
            'menu_group_id' => $produkGroup?->id,
            'nama_menu' => 'Produk',
            'permission_group_id' => 13,
            'icon' => 'ri-shopping-bag-3-line',
            'status' => '1',
            'sort' => '1',
        ]);

        $promo = Menu::create([
            'menu_group_id' => $produkGroup?->id,
            'nama_menu' => 'Promo',
            'permission_group_id' => 14,
            'icon' => 'ri-gift-line',
            'status' => '1',
            'sort' => '2',
        ]);

        Menu::create([
            'menu_id' => $produk->id,
            'nama_menu' => 'Kategori Produk',
            'permission_group_id' => 12,
            'href' => '/product_categories',
            'status' => '1',
            'sort' => '1',
        ]);

        Menu::create([
            'menu_id' => $produk->id,
            'nama_menu' => 'Produk',
            'permission_group_id' => 13,
            'href' => '/products',
            'status' => '1',
            'sort' => '2',
        ]);

        Menu::create([
            'menu_id' => $promo->id,
            'nama_menu' => 'Promo',
            'permission_group_id' => 14,
            'href' => '/promo',
            'status' => '1',
            'sort' => '3',
        ]);

        // === Menu 2: Meja (Group: MEJA) ===
        Menu::create([
            'menu_group_id' => $mejaGroup?->id,
            'nama_menu' => 'Management Meja',
            'permission_group_id' => 15,
            'icon' => 'ri-layout-grid-line',
            'href' => '/tables',
            'status' => '1',
            'sort' => '1',
        ]);

        // === Menu 3: Artikel (Group: KONTEN) ===
        $artikel = Menu::create([
            'menu_group_id' => $kontenGroup?->id,
            'nama_menu' => 'Artikel',
            'permission_group_id' => 7,
            'icon' => 'ri-article-line',
            'status' => '1',
            'sort' => '1',
        ]);

        Menu::create([
            'menu_id' => $artikel->id,
            'nama_menu' => 'Artikel Kategori',
            'permission_group_id' => 7,
            'href' => '/article_categories',
            'status' => '1',
            'sort' => '1',
        ]);

        Menu::create([
            'menu_id' => $artikel->id,
            'nama_menu' => 'Artikel',
            'permission_group_id' => 7,
            'href' => '/article',
            'status' => '1',
            'sort' => '2',
        ]);

        // === Menu 4: Setting (Group: PENGATURAN) ===
        $setting = Menu::create([
            'menu_group_id' => $pengaturanGroup?->id,
            'nama_menu' => 'Setting',
            'permission_group_id' => 8,
            'icon' => 'ri-settings-3-line',
            'status' => '1',
            'sort' => '1',
        ]);

        // Submenu User Management
        $userManagement = Menu::create([
            'menu_id' => $setting->id,
            'nama_menu' => 'User Management',
            'permission_group_id' => 8,
            'status' => '1',
            'sort' => '1',
        ]);

        // Level 3 dari User Management
        Menu::create([
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Users',
            'permission_group_id' => 1,
            'href' => '/user',
            'status' => '1',
            'sort' => '1',
        ]);

        Menu::create([
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Permission Group',
            'permission_group_id' => 8,
            'href' => '/permissiongroup',
            'status' => '1',
            'sort' => '2',
        ]);

        Menu::create([
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Permissions',
            'permission_group_id' => 8,
            'href' => '/permission',
            'status' => '1',
            'sort' => '3',
        ]);

        Menu::create([
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Roles',
            'permission_group_id' => 8,
            'href' => '/role',
            'status' => '1',
            'sort' => '4',
        ]);

        Menu::create([
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Outlets',
            'permission_group_id' => 9,
            'href' => '/outlet',
            'status' => '1',
            'sort' => '5',
        ]);

        // Submenu Web Setting (langsung di bawah Setting)
        Menu::create([
            'menu_id' => $setting->id,
            'nama_menu' => 'Web Setting',
            'permission_group_id' => 8,
            'href' => '/setting',
            'status' => '1',
            'sort' => '2',
        ]);

        Menu::create([
            'menu_id' => $setting->id,
            'nama_menu' => 'Menu Management',
            'permission_group_id' => 8,
            'href' => '/menu',
            'status' => '1',
            'sort' => '3',
        ]);

        Menu::create([
            'menu_id' => $setting->id,
            'nama_menu' => 'Menu Group',
            'permission_group_id' => 8,
            'href' => '/menugroup',
            'status' => '1',
            'sort' => '4',
        ]);

        // Stock Management
        $stockManagement = Menu::create([
            'uuid' => Str::uuid(),
            'nama_menu' => 'Stock Management',
            'icon' => 'ri-stack-line',
            'permission_group_id' => PermissionGroup::where('name', 'Product Stock')->first()->id,
            'status' => true,
            'sort' => 3,
        ]);

        Menu::create([
            'uuid' => Str::uuid(),
            'nama_menu' => 'Product Stocks',
            'menu_id' => $stockManagement->id,
            'permission_group_id' => PermissionGroup::where('name', 'Product Stock')->first()->id,
            'href' => '/product-stocks',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => Str::uuid(),
            'nama_menu' => 'Stock Movements',
            'menu_id' => $stockManagement->id,
            'permission_group_id' => PermissionGroup::where('name', 'Stock Movement')->first()->id,
            'href' => '/stock-movements',
            'status' => true,
            'sort' => 2,
        ]);

        // Customer Promo
        $customerPromo = Menu::create([
            'uuid' => Str::uuid(),
            'nama_menu' => 'Customer Promo',
            'icon' => 'ri-gift-line',
            'permission_group_id' => PermissionGroup::where('name', 'Customer Promo')->first()->id,
            'status' => true,
            'sort' => 4,
        ]);

        Menu::create([
            'uuid' => Str::uuid(),
            'nama_menu' => 'Customer Promo',
            'menu_id' => $customerPromo->id,
            'permission_group_id' => PermissionGroup::where('name', 'Customer Promo')->first()->id,
            'href' => '/customer-promos',
            'status' => true,
            'sort' => 1,
        ]);
    }
}
