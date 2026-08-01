<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\PermissionGroup;
use App\Models\Menu;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolePermissionSeeder::class);

        // Seed Outlets
        $outletBandung = Outlet::create([
            'name' => 'Outlet Bandung',
            'address' => 'Jl. Braga No. 10, Bandung',
            'phone' => '022-123456',
            'status' => true,
        ]);

        $outletJakarta = Outlet::create([
            'name' => 'Outlet Jakarta',
            'address' => 'Jl. Sudirman No. 50, Jakarta',
            'phone' => '021-987654',
            'status' => true,
        ]);

        $admin = User::create([
            'username' => 'farel',
            'name' => 'tagor',
            'email' => 'techareaproduction@gmail.com',
            'email_verified_at' => '2022-08-16 20:57:19',
            'password' => Hash::make('admin123'),
        ]);

        $admin->assignRole('Super Admin');

        $owner = User::create([
            'username' => 'owner',
            'name' => 'Owner Restoran',
            'email' => 'owner@gmail.com',
            'email_verified_at' => '2022-08-16 20:57:19',
            'password' => Hash::make('admin123'),
        ]);
        $owner->assignRole('Owner');
        $owner->outlets()->attach([$outletBandung->id, $outletJakarta->id]);

        $employee = User::create([
            'username' => 'employee',
            'name' => 'Karyawan Bandung',
            'email' => 'employee@gmail.com',
            'email_verified_at' => '2022-08-16 20:57:19',
            'password' => Hash::make('admin123'),
            'current_outlet_id' => $outletBandung->id,
        ]);
        $employee->assignRole('Employee');
        $employee->outlets()->attach($outletBandung->id);

        $user = User::create([
            'username' => 'user',
            'name' => 'User',
            'email' => 'user@gmail.com',
            'email_verified_at' => '2022-08-16 20:57:19',
            'password' => Hash::make('user12345'),
        ]);

        $user->assignRole('User');

        $this->call(MenuGroupSeeder::class);
        $this->call(MenuSeeder::class);

        // Seed Sample Articles for Scoping
        $category = ArticleCategory::create([
            'name' => 'Culinary',
        ]);

        Article::create([
            'article_category_id' => $category->id,
            'user_id' => $admin->id,
            'image_path' => 'article-images/sample-braga.jpg',
            'title' => 'Resep Rahasia Braga Bandung',
            'excerpt' => 'Menu khas Braga Bandung yang legendaris.',
            'content' => '<p>Ini adalah artikel tentang menu khas Braga Bandung.</p>',
            'published_at' => now(),
            'highlite' => true,
            'outlet_id' => $outletBandung->id,
        ]);

        Article::create([
            'article_category_id' => $category->id,
            'user_id' => $admin->id,
            'image_path' => 'article-images/sample-sudirman.jpg',
            'title' => 'Kopi Jakarta Signature',
            'excerpt' => 'Kopi khas Sudirman Jakarta yang nikmat.',
            'content' => '<p>Ini adalah artikel tentang kopi khas Sudirman Jakarta.</p>',
            'published_at' => now(),
            'highlite' => false,
            'outlet_id' => $outletJakarta->id,
        ]);

        // Stock Management
        $stockManagement = Menu::create([
            'uuid' => Str::uuid(),
            'nama_menu' => 'Stock Management',
            'icon' => 'ri-stack-line',
            'permission_group_id' => 16, // Product Stock
            'status' => true,
            'sort' => 3,
        ]);

        Menu::create([
            'uuid' => Str::uuid(),
            'nama_menu' => 'Product Stocks',
            'menu_id' => $stockManagement->id,
            'permission_group_id' => 16, // Product Stock
            'href' => '/product-stocks',
            'status' => true,
            'sort' => 1,
        ]);

        Menu::create([
            'uuid' => Str::uuid(),
            'nama_menu' => 'Stock Movements',
            'menu_id' => $stockManagement->id,
            'permission_group_id' => 17, // Stock Movement
            'href' => '/stock-movements',
            'status' => true,
            'sort' => 2,
        ]);
    }
}
