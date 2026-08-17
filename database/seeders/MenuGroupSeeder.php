<?php

namespace Database\Seeders;

use App\Models\MenuGroup;
use Illuminate\Database\Seeder;

class MenuGroupSeeder extends Seeder
{
    public function run()
    {
        // Management Product
        MenuGroup::updateOrCreate(
            ['name' => 'Management Product'],
            [
                'permission_group_id' => 13,
                'sort' => 1,
                'status' => 1,
            ]
        );

        // Management Table
        MenuGroup::updateOrCreate(
            ['name' => 'Management Table'],
            [
                'permission_group_id' => 15,
                'sort' => 2,
                'status' => 1,
            ]
        );

        // Management Content
        MenuGroup::updateOrCreate(
            ['name' => 'Management Content'],
            [
                'permission_group_id' => 10,
                'sort' => 3,
                'status' => 1,
            ]
        );

        // Setting
        MenuGroup::updateOrCreate(
            ['name' => 'Setting'],
            [
                'permission_group_id' => 8,
                'sort' => 4,
                'status' => 1,
            ]
        );

        // Management Report
        MenuGroup::updateOrCreate(
            ['name' => 'Management Report'],
            [
                'permission_group_id' => 18,
                'sort' => 6,
                'status' => 1,
            ]
        );
    }
}