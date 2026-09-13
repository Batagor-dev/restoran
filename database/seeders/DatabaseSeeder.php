<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        $outletBandung = Outlet::firstOrCreate(
            ['name' => 'Outlet Bandung'],
            [
                'address' => 'Jl. Braga No. 10, Bandung',
                'phone' => '022-123456',
                'status' => true,
            ]
        );

        $outletJakarta = Outlet::firstOrCreate(
            ['name' => 'Outlet Jakarta'],
            [
                'address' => 'Jl. Sudirman No. 50, Jakarta',
                'phone' => '021-987654',
                'status' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['username' => 'farel'],
            [
                'name' => 'tagor',
                'email' => 'techareaproduction@gmail.com',
                'email_verified_at' => '2022-08-16 20:57:19',
                'password' => Hash::make('admin123'),
            ]
        );
        $admin->syncRoles(['Super Admin']);

        $owner = User::firstOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Owner Restoran',
                'email' => 'owner@gmail.com',
                'email_verified_at' => '2022-08-16 20:57:19',
                'password' => Hash::make('admin123'),
            ]
        );
        $owner->syncRoles(['Owner']);
        $owner->outlets()->syncWithoutDetaching([$outletBandung->id, $outletJakarta->id]);

        $employee = User::firstOrCreate(
            ['username' => 'employee'],
            [
                'name' => 'Karyawan Bandung',
                'email' => 'employee@gmail.com',
                'email_verified_at' => '2022-08-16 20:57:19',
                'password' => Hash::make('admin123'),
                'current_outlet_id' => $outletBandung->id,
            ]
        );
        $employee->syncRoles(['Employee']);
        $employee->outlets()->syncWithoutDetaching([$outletBandung->id]);

        $user = User::firstOrCreate(
            ['username' => 'user'],
            [
                'name' => 'User',
                'email' => 'user@gmail.com',
                'email_verified_at' => '2022-08-16 20:57:19',
                'password' => Hash::make('user12345'),
            ]
        );
        $user->syncRoles(['User']);

        $this->call(MenuGroupSeeder::class);
        $this->call(MenuSeeder::class);

        // Seed Sample Articles for Scoping
        $category = ArticleCategory::firstOrCreate([
            'name' => 'Culinary',
        ]);

        Article::firstOrCreate(
            ['title' => 'Resep Rahasia Braga Bandung'],
            [
                'article_category_id' => $category->id,
                'user_id' => $admin->id,
                'image_path' => 'article-images/sample-braga.jpg',
                'excerpt' => 'Menu khas Braga Bandung yang legendaris.',
                'content' => '<p>Ini adalah artikel tentang menu khas Braga Bandung.</p>',
                'published_at' => now(),
                'highlite' => true,
                'outlet_id' => $outletBandung->id,
            ]
        );

        Article::firstOrCreate(
            ['title' => 'Kopi Jakarta Signature'],
            [
                'article_category_id' => $category->id,
                'user_id' => $admin->id,
                'image_path' => 'article-images/sample-sudirman.jpg',
                'excerpt' => 'Kopi khas Sudirman Jakarta yang nikmat.',
                'content' => '<p>Ini adalah artikel tentang kopi khas Sudirman Jakarta.</p>',
                'published_at' => now(),
                'highlite' => false,
                'outlet_id' => $outletJakarta->id,
            ]
        );
    }
}
