<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Ambil / buat role secara aman:
     * - jika pernah dihapus (SoftDeletes) -> restore, JANGAN buat baris baru
     *   (mencegah putusnya relasi model_has_roles ke user).
     */
    private function ensureRole(string $name): Role
    {
        $role = Role::withTrashed()->firstOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            ['uuid' => Str::uuid()->toString()]
        );

        if ($role->trashed()) {
            $role->restore();
        }

        return $role;
    }

    private function ensureGroup(string $name): PermissionGroup
    {
        return PermissionGroup::firstOrCreate(['name' => $name]);
    }

    public function run()
    {
        // Reset cache permission Spatie di awal & akhir agar hasil konsisten
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissiongroups = [
            'User', 'Role', 'Permission Group', 'Permission', 'Menu',
            'Article Category', 'Article', 'Setting', 'Outlet', 'Konten',
            'Pengaturan', 'Product Category', 'Product', 'Promo', 'Table',
            'Product Stock', 'Stock Movement', 'Customer Promo', 'Order',
            'Order Item', 'POS', 'Customer', 'Kitchen',
            'Transaction', // dibuat via ensureGroup (id non-hardcode)
            'Report',
        ];

        foreach ($permissiongroups as $permissiongroup) {
            $this->ensureGroup($permissiongroup);
        }

        $permissions = [
            'User Access', 'User Detail', 'User Create', 'User Update',
            'User Banned', 'User Role Create',
            'Role Access', 'Role Detail', 'Role Create', 'Role Update', 'Role Delete',
            'Permission Group Access', 'Permission Group Create', 'Permission Group Update', 'Permission Group Delete',
            'Permission Access', 'Permission Create', 'Permission Update', 'Permission Delete',
            'Menu Access', 'Menu Create', 'Menu Update', 'Menu Delete',
            'Article Category Access', 'Article Category Create', 'Article Category Update', 'Article Category Delete',
            'Article Access', 'Article Detail', 'Article Create', 'Article Update', 'Article Delete',
            'Setting Access', 'Setting Detail', 'Setting Create', 'Setting Update', 'Setting Delete',
            'Outlet Access', 'Outlet Create', 'Outlet Update', 'Outlet Delete',
            'Konten Access', 'Pengaturan Access',
            'Product Category Access', 'Product Category Create', 'Product Category Update', 'Product Category Delete',
            'Product Access', 'Product Create', 'Product Update', 'Product Delete',
            'Promo Access', 'Promo Create', 'Promo Update', 'Promo Delete',
            'Table Access', 'Table Create', 'Table Update', 'Table Delete',
            'Product Stock Access', 'Product Stock Create', 'Product Stock Update', 'Product Stock Delete',
            'Stock Movement Access', 'Stock Movement Create', 'Stock Movement Delete',
            'Customer Promo Access', 'Customer Promo Create', 'Customer Promo Update', 'Customer Promo Delete',
            'Order Access', 'Order Create', 'Order Update', 'Order Delete',
            'Order Item Access', 'Order Item Create', 'Order Item Update', 'Order Item Delete',
            'POS Access',
            'Kitchen Access',
        ];

        // Grup mengikuti urutan nama (satu permission pertama per grup cukup representatif)
        $groupByName = [
            'User' => 'User', 'Role' => 'Role', 'Permission Group' => 'Permission Group',
            'Permission' => 'Permission', 'Menu' => 'Menu', 'Article Category' => 'Article Category',
            'Article' => 'Article', 'Setting' => 'Setting', 'Outlet' => 'Outlet',
            'Konten' => 'Konten', 'Pengaturan' => 'Pengaturan', 'Product Category' => 'Product Category',
            'Product' => 'Product', 'Promo' => 'Promo', 'Table' => 'Table',
            'Product Stock' => 'Product Stock', 'Stock Movement' => 'Stock Movement',
            'Customer Promo' => 'Customer Promo', 'Order' => 'Order', 'Order Item' => 'Order Item',
            'POS' => 'POS', 'Kitchen' => 'Kitchen',
        ];

        foreach ($permissions as $name) {
            $groupName = $groupByName[$name] ?? null;

            Permission::withTrashed()->updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['deleted_at' => null] + ($groupName ? [
                    'permission_group_id' => $this->ensureGroup($groupName)->id,
                ] : [])
            );

            if (str_starts_with($name, 'Transaction ') || str_starts_with($name, 'Report ')) {
                // permission modul baru diletakkan di grup masing-masing
                $target = str_starts_with($name, 'Transaction ') ? 'Transaction' : 'Report';
                Permission::where('name', $name)->where('guard_name', 'web')
                    ->update(['permission_group_id' => $this->ensureGroup($target)->id, 'deleted_at' => null]);
            }
        }

        // Permission modul tambahan (nama mengandung spasi + suffix grup lama sudah tidak dipakai)
        foreach ([
            'Transaction' => ['Transaction Access', 'Transaction Refund', 'Transaction Void', 'Transaction Report'],
            'Report' => ['Report Access'],
        ] as $groupName => $permNames) {
            $group = $this->ensureGroup($groupName);

            foreach ($permNames as $name) {
                Permission::withTrashed()->updateOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['permission_group_id' => $group->id, 'deleted_at' => null]
                );
            }
        }

        // ---- Roles ----
        $superAdmin = $this->ensureRole('Super Admin');
        $superAdmin->syncPermissions(Permission::all());

        $owner = $this->ensureRole('Owner');
        $owner->syncPermissions(Permission::all());

        $employee = $this->ensureRole('Employee');
        $employee->givePermissionTo([
            'Article Access', 'Article Create', 'Article Update',
            'Product Category Access', 'Product Category Create', 'Product Category Update',
            'Promo Access', 'Promo Create', 'Promo Update',
            'Table Access', 'Table Create', 'Table Update',
        ]);

        $userRole = $this->ensureRole('User');
        $userRole->givePermissionTo('Article Access');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
