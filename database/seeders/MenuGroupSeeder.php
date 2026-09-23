<?php

namespace Database\Seeders;

use App\Models\MenuGroup;
use Illuminate\Database\Seeder;

class MenuGroupSeeder extends Seeder
{
    public function run()
    {
        MenuGroup::updateOrCreate(
            ['name' => 'Operasional'],
            [
                'permission_group_id' => null,
                'sort' => 1,
                'status' => 1,
            ]
        );

        MenuGroup::updateOrCreate(
            ['name' => 'Produk & Inventaris'],
            [
                'permission_group_id' => 13,
                'sort' => 2,
                'status' => 1,
            ]
        );

        MenuGroup::updateOrCreate(
            ['name' => 'Laporan & Transaksi'],
            [
                'permission_group_id' => 25,
                'sort' => 3,
                'status' => 1,
            ]
        );

        MenuGroup::updateOrCreate(
            ['name' => 'Pelanggan & Promosi'],
            [
                'permission_group_id' => 22,
                'sort' => 4,
                'status' => 1,
            ]
        );

        MenuGroup::updateOrCreate(
            ['name' => 'Konten & Publikasi'],
            [
                'permission_group_id' => 7,
                'sort' => 5,
                'status' => 1,
            ]
        );

        MenuGroup::updateOrCreate(
            ['name' => 'Pengaturan Sistem'],
            [
                'permission_group_id' => 8,
                'sort' => 6,
                'status' => 1,
            ]
        );

        // Hapus grup lama yang sudah tidak terpakai agar database bersih
        $activeGroups = [
            'Operasional',
            'Produk & Inventaris',
            'Laporan & Transaksi',
            'Pelanggan & Promosi',
            'Konten & Publikasi',
            'Pengaturan Sistem',
        ];
        MenuGroup::whereNotIn('name', $activeGroups)->delete();
    }
}