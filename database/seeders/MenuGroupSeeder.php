<?php

namespace Database\Seeders;

use App\Models\MenuGroup;
use Illuminate\Database\Seeder;

class MenuGroupSeeder extends Seeder
{
    public function run()
    {
        MenuGroup::firstOrCreate(
            ['name' => 'KONTEN'],
            [
                'sort' => 1,
                'status' => 1,
            ]
        );

        MenuGroup::firstOrCreate(
            ['name' => 'PENGATURAN'],
            [
                'sort' => 2,
                'status' => 1,
            ]
        );
    }
}
