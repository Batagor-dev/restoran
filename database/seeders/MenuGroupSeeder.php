<?php

namespace Database\Seeders;

use App\Models\MenuGroup;
use Illuminate\Database\Seeder;

class MenuGroupSeeder extends Seeder
{
    public function run()
    {
        MenuGroup::updateOrCreate(
            ['name' => 'PRODUK'],
            [
                'permission_group_id' => 13,
                'sort' => 1,
                'status' => 1,
            ]
        );

        MenuGroup::updateOrCreate(
            ['name' => 'KONTEN'],
            [
                'permission_group_id' => 10,
                'sort' => 2,
                'status' => 1,
            ]
        );

        MenuGroup::updateOrCreate(
            ['name' => 'PENGATURAN'],
            [
                'permission_group_id' => 8,
                'sort' => 3,
                'status' => 1,
            ]
        );
    }
}
