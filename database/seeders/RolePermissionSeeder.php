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
            'User',
            'Role',
            'Permission Group',
            'Permission',
            'Menu',
            'Article Category',
            'Article',
            'Setting',
            'Outlet',
            'Konten',
            'Pengaturan',
        ];

        foreach ($permissiongroups as $permissiongroup) {
            PermissionGroup::create([
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

        ];

        foreach ($permissions as $permission) {
            $permission_array = explode('-', $permission);
            Permission::create([
                'name' => $permission_array[0],
                'permission_group_id' => $permission_array[1],
            ]);
        }

        $superAdmin = Role::create([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        $superAdmin->givePermissionTo(Permission::all());

        $owner = Role::create([
            'name' => 'Owner',
            'guard_name' => 'web',
        ]);
        $owner->givePermissionTo(Permission::all());

        $employee = Role::create([
            'name' => 'Employee',
            'guard_name' => 'web',
        ]);
        $employee->givePermissionTo([
            'Article Access',
            'Article Create',
            'Article Update',
        ]);

        $role = Role::create([
            'name' => 'User',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo('Article Access');
    }
}
