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
        // Bersihkan data menu lama agar tidak ada duplikasi saat seeder dijalankan ulang
        Menu::withTrashed()->forceDelete();

        // 1. Ambil Menu Groups
        $operasionalGroup = MenuGroup::where('name', 'Operasional')->first();
        $produkGroup = MenuGroup::where('name', 'Produk & Inventaris')->first();
        $laporanGroup = MenuGroup::where('name', 'Laporan & Transaksi')->first();
        $pelangganGroup = MenuGroup::where('name', 'Pelanggan & Promosi')->first();
        $kontenGroup = MenuGroup::where('name', 'Konten & Publikasi')->first();
        $pengaturanGroup = MenuGroup::where('name', 'Pengaturan Sistem')->first();

        // Helper untuk mencari ID permission group berdasarkan nama
        $pg = function (string $name) {
            return PermissionGroup::where('name', $name)->value('id');
        };

        // ============================================================
        // 1. OPERASIONAL
        // ============================================================
        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $operasionalGroup?->id,
            'nama_menu' => 'Kasir',
            'icon' => 'ri-shopping-cart-2-line',
            'permission_group_id' => $pg('POS') ?? 21,
            'href' => '/pos',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $operasionalGroup?->id,
            'nama_menu' => 'Kitchen',
            'icon' => 'ri-restaurant-2-line',
            'permission_group_id' => $pg('Kitchen') ?? 23,
            'href' => '/kitchen',
            'status' => true,
            'sort' => 2,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $operasionalGroup?->id,
            'nama_menu' => 'Daftar Pesanan',
            'icon' => 'ri-file-list-3-line',
            'permission_group_id' => $pg('Order') ?? 19,
            'href' => '/orders',
            'status' => true,
            'sort' => 3,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $operasionalGroup?->id,
            'nama_menu' => 'Manajemen Meja',
            'icon' => 'ri-layout-grid-line',
            'permission_group_id' => $pg('Table') ?? 15,
            'href' => '/tables',
            'status' => true,
            'sort' => 4,
        ]);

        // ============================================================
        // 2. PRODUK & INVENTARIS
        // ============================================================
        $katalogProduk = Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $produkGroup?->id,
            'nama_menu' => 'Katalog Produk',
            'icon' => 'ri-shopping-bag-3-line',
            'permission_group_id' => $pg('Product') ?? 13,
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $katalogProduk->id,
            'nama_menu' => 'Daftar Produk',
            'permission_group_id' => $pg('Product') ?? 13,
            'href' => '/products',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $katalogProduk->id,
            'nama_menu' => 'Kategori Produk',
            'permission_group_id' => $pg('Product Category') ?? 12,
            'href' => '/product_categories',
            'status' => true,
            'sort' => 2,
        ]);

        $manajemenStok = Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $produkGroup?->id,
            'nama_menu' => 'Inventaris & Stok',
            'icon' => 'ri-stack-line',
            'permission_group_id' => $pg('Product Stock') ?? 16,
            'status' => true,
            'sort' => 2,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $manajemenStok->id,
            'nama_menu' => 'Stok Outlet',
            'permission_group_id' => $pg('Product Stock') ?? 16,
            'href' => '/product-stocks',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $manajemenStok->id,
            'nama_menu' => 'Mutasi Stok',
            'permission_group_id' => $pg('Stock Movement') ?? 17,
            'href' => '/stock-movements',
            'status' => true,
            'sort' => 2,
        ]);

        // ============================================================
        // 3. LAPORAN & TRANSAKSI
        // ============================================================
        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $laporanGroup?->id,
            'nama_menu' => 'Riwayat Transaksi',
            'icon' => 'ri-receipt-line',
            'permission_group_id' => $pg('Transaction') ?? 24,
            'href' => '/transactions',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $laporanGroup?->id,
            'nama_menu' => 'Laporan Bisnis',
            'icon' => 'ri-bar-chart-box-line',
            'permission_group_id' => $pg('Report') ?? 25,
            'href' => '/reports',
            'status' => true,
            'sort' => 2,
        ]);

        // ============================================================
        // 4. PELANGGAN & PROMOSI
        // ============================================================
        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $pelangganGroup?->id,
            'nama_menu' => 'Data Pelanggan',
            'icon' => 'ri-user-smile-line',
            'permission_group_id' => $pg('Customer') ?? 22,
            'href' => '/customers',
            'status' => true,
            'sort' => 1,
        ]);

        $promosi = Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $pelangganGroup?->id,
            'nama_menu' => 'Promosi & Diskon',
            'icon' => 'ri-gift-line',
            'permission_group_id' => $pg('Promo') ?? 14,
            'status' => true,
            'sort' => 2,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $promosi->id,
            'nama_menu' => 'Promo Global',
            'permission_group_id' => $pg('Promo') ?? 14,
            'href' => '/promo',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $promosi->id,
            'nama_menu' => 'Promo Khusus Member',
            'permission_group_id' => $pg('Customer Promo') ?? 18,
            'href' => '/customer-promos',
            'status' => true,
            'sort' => 2,
        ]);

        // ============================================================
        // 5. KONTEN & PUBLIKASI
        // ============================================================
        $artikel = Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $kontenGroup?->id,
            'nama_menu' => 'Artikel & Berita',
            'icon' => 'ri-article-line',
            'permission_group_id' => $pg('Article') ?? 7,
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $artikel->id,
            'nama_menu' => 'Daftar Artikel',
            'permission_group_id' => $pg('Article') ?? 7,
            'href' => '/article',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $artikel->id,
            'nama_menu' => 'Kategori Artikel',
            'permission_group_id' => $pg('Article Category') ?? 6,
            'href' => '/article_categories',
            'status' => true,
            'sort' => 2,
        ]);

        // ============================================================
        // 6. PENGATURAN SISTEM
        // ============================================================
        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $pengaturanGroup?->id,
            'nama_menu' => 'Kelola Outlet',
            'icon' => 'ri-store-2-line',
            'permission_group_id' => $pg('Outlet') ?? 9,
            'href' => '/outlet',
            'status' => true,
            'sort' => 1,
        ]);

        $userManagement = Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $pengaturanGroup?->id,
            'nama_menu' => 'Manajemen Pengguna',
            'icon' => 'ri-shield-user-line',
            'permission_group_id' => $pg('User') ?? 1,
            'status' => true,
            'sort' => 2,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Daftar Pengguna',
            'permission_group_id' => $pg('User') ?? 1,
            'href' => '/user',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Peran (Roles)',
            'permission_group_id' => $pg('Role') ?? 2,
            'href' => '/role',
            'status' => true,
            'sort' => 2,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Hak Akses',
            'permission_group_id' => $pg('Permission') ?? 4,
            'href' => '/permission',
            'status' => true,
            'sort' => 3,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $userManagement->id,
            'nama_menu' => 'Grup Hak Akses',
            'permission_group_id' => $pg('Permission Group') ?? 3,
            'href' => '/permissiongroup',
            'status' => true,
            'sort' => 4,
        ]);

        $navigasiMenu = Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $pengaturanGroup?->id,
            'nama_menu' => 'Navigasi Menu',
            'icon' => 'ri-menu-search-line',
            'permission_group_id' => $pg('Menu') ?? 5,
            'status' => true,
            'sort' => 3,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $navigasiMenu->id,
            'nama_menu' => 'Daftar Menu',
            'permission_group_id' => $pg('Menu') ?? 5,
            'href' => '/menu',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_id' => $navigasiMenu->id,
            'nama_menu' => 'Grup Menu',
            'permission_group_id' => $pg('Menu') ?? 5,
            'href' => '/menugroup',
            'status' => true,
            'sort' => 2,
        ]);

        Menu::create([
            'uuid' => (string) Str::uuid(),
            'menu_group_id' => $pengaturanGroup?->id,
            'nama_menu' => 'Pengaturan Web',
            'icon' => 'ri-settings-3-line',
            'permission_group_id' => $pg('Setting') ?? 8,
            'href' => '/setting',
            'status' => true,
            'sort' => 4,
        ]);
    }
}