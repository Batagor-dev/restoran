<?php

namespace Database\Seeders;

use App\Models\MenuGroup;
use App\Models\PermissionGroup;
use Illuminate\Database\Seeder;

class MenuGroupSeeder extends Seeder
{
    public function run()
    {
        $productGroup = PermissionGroup::where('name', 'Product')->first();
        $tableGroup = PermissionGroup::where('name', 'Table')->first();
        $contentGroup = PermissionGroup::where('name', 'Konten')->first();
        $settingGroup = PermissionGroup::where('name', 'Setting')->first();
        $reportGroup = PermissionGroup::where('name', 'Report')->first()
            ?? PermissionGroup::where('name', 'Order')->first();

        // Management Product
        MenuGroup::updateOrCreate(
            ['name' => 'Management Product'],
            [
                'permission_group_id' => $productGroup?->id ?? 13,
                'sort' => 1,
                'status' => 1,
            ]
        );

        // Management Table
        MenuGroup::updateOrCreate(
            ['name' => 'Management Table'],
            [
                'permission_group_id' => $tableGroup?->id ?? 15,
                'sort' => 2,
                'status' => 1,
            ]
        );

        // Management Content
        MenuGroup::updateOrCreate(
            ['name' => 'Management Content'],
            [
                'permission_group_id' => $contentGroup?->id ?? 10,
                'sort' => 3,
                'status' => 1,
            ]
        );

        // Setting
        MenuGroup::updateOrCreate(
            ['name' => 'Setting'],
            [
                'permission_group_id' => $settingGroup?->id ?? 8,
                'sort' => 4,
                'status' => 1,
            ]
        );

        // Management Report
        MenuGroup::updateOrCreate(
            ['name' => 'Management Report'],
            [
                'permission_group_id' => $reportGroup?->id ?? 25,
                'sort' => 6,
                'status' => 1,
            ]
        );
    }
}