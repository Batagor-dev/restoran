<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ProductPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Permission Group untuk Product Category
        $categoryGroup = PermissionGroup::firstOrCreate([
            'name' => 'Product Category'
        ]);

        // 2. Buat Permission Group untuk Product
        $productGroup = PermissionGroup::firstOrCreate([
            'name' => 'Product'
        ]);

        // 3. Buat Permission untuk Product Category
        $categoryPermissions = [
            'Product Category Access',
            'Product Category Create',
            'Product Category Update',
            'Product Category Delete',
        ];

        foreach ($categoryPermissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'permission_group_id' => $categoryGroup->id,
                'guard_name' => 'web'
            ]);
        }

        // 4. Buat Permission untuk Product
        $productPermissions = [
            'Product Access',
            'Product Create',
            'Product Update',
            'Product Delete',
        ];

        foreach ($productPermissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'permission_group_id' => $productGroup->id,
                'guard_name' => 'web'
            ]);
        }

        // 5. Assign ke Super Admin & Owner
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $owner = Role::where('name', 'Owner')->first();

        $allPermissions = Permission::whereIn('name', array_merge($categoryPermissions, $productPermissions))->get();

        if ($superAdmin) {
            $superAdmin->givePermissionTo($allPermissions);
        }

        if ($owner) {
            $owner->givePermissionTo($allPermissions);
        }

        $this->command->info('✅ Permission Product & Category berhasil dibuat!');
    }
}