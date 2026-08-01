<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissiongroups = [
            'User',                // 1
            'Role',                // 2
            'Permission Group',    // 3
            'Permission',          // 4
            'Menu',                // 5
            'Article Category',    // 6
            'Article',             // 7
            'Setting',             // 8
            'Outlet',              // 9
            'Konten',              // 10
            'Pengaturan',          // 11
            'Product Category',    // 12
            'Product',             // 13
            'Promo',               // 14
            'Table',               // 15
            'Product Stock',       // 16
            'Stock Movement',      // 17
            'Customer Promo',      // 18
            'Order',               // 19
            'Order Item',          // 20
        ];

        foreach ($permissiongroups as $permissiongroup) {
            PermissionGroup::firstOrCreate([
                'name' => $permissiongroup,
            ]);
        }

        $permissions = [
            'User Access-1',
            'User Detail-1',
            'User Create-1',
            'User Update-1',
            'User Banned-1',
            'User Role Create-1',
            'Role Access-2',
            'Role Detail-2',
            'Role Create-2',
            'Role Update-2',
            'Role Delete-2',
            'Permission Group Access-3',
            'Permission Group Create-3',
            'Permission Group Update-3',
            'Permission Group Delete-3',
            'Permission Access-4',
            'Permission Create-4',
            'Permission Update-4',
            'Permission Delete-4',
            'Menu Access-5',
            'Menu Create-5',
            'Menu Update-5',
            'Menu Delete-5',
            'Article Category Access-6',
            'Article Category Create-6',
            'Article Category Update-6',
            'Article Category Delete-6',
            'Article Access-7',
            'Article Detail-7',
            'Article Create-7',
            'Article Update-7',
            'Article Delete-7',
            'Setting Access-8',
            'Setting Detail-8',
            'Setting Create-8',
            'Setting Update-8',
            'Setting Delete-8',
            'Outlet Access-9',
            'Outlet Create-9',
            'Outlet Update-9',
            'Outlet Delete-9',
            'Konten Access-10',
            'Pengaturan Access-11',
            'Product Category Access-12',
            'Product Category Create-12',
            'Product Category Update-12',
            'Product Category Delete-12',
            'Product Access-13',
            'Product Create-13',
            'Product Update-13',
            'Product Delete-13',
            'Promo Access-14',
            'Promo Create-14',
            'Promo Update-14',
            'Promo Delete-14',
            'Table Access-15',
            'Table Create-15',
            'Table Update-15',
            'Table Delete-15',
            'Product Stock Access-16',
            'Product Stock Create-16',
            'Product Stock Update-16',
            'Product Stock Delete-16',
            'Stock Movement Access-17',
            'Stock Movement Create-17',
            'Stock Movement Delete-17',
            'Customer Promo Access-18',
            'Customer Promo Create-18',
            'Customer Promo Update-18',
            'Customer Promo Delete-18',
            'Order Access-19',
            'Order Create-19',
            'Order Update-19',
            'Order Delete-19',
            'Order Item Access-20',
            'Order Item Create-20',
            'Order Item Update-20',
            'Order Item Delete-20',
        ];

        foreach ($permissions as $permission) {
            $permission_array = explode('-', $permission);
            Permission::firstOrCreate([
                'name' => $permission_array[0],
                'permission_group_id' => $permission_array[1],
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);
        $superAdmin->syncPermissions(Permission::all());

        $owner = Role::firstOrCreate([
            'name' => 'Owner',
            'guard_name' => 'web',
        ]);
        $owner->syncPermissions(Permission::all());

        $employee = Role::firstOrCreate([
            'name' => 'Employee',
            'guard_name' => 'web',
        ]);
        $employee->givePermissionTo([
            'Article Access',
            'Article Create',
            'Article Update',
            'Product Category Access',
            'Product Category Create',
            'Product Category Update',
            'Promo Access',
            'Promo Create',
            'Promo Update',
            'Table Access',
            'Table Create',
            'Table Update',
        ]);

        $role = Role::firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo('Article Access');
    }
}
