<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Role & permission dasar sesuai Bagian 6 (RBAC) prompt pengembangan.
     */
    public function run(): void
    {
        $permissions = [
            'psn.view', 'psn.manage',
            'profil.manage',
            'ro.manage',
            'risiko.manage',
            'regulasi.manage',
            'pengendalian.manage', 'pengendalian.approve',
            'perencanaan.manage', 'perencanaan.approve',
            'memo.manage',
            'pengguna.manage',
            'laporan.export',
            'sinkronisasi.manage',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'Super Admin' => $permissions,
            'Admin Pengendalian' => [
                'psn.view', 'ro.manage', 'risiko.manage', 'regulasi.manage',
                'pengendalian.manage', 'pengendalian.approve', 'memo.manage', 'laporan.export',
            ],
            'Admin Perencanaan' => [
                'psn.view', 'profil.manage', 'perencanaan.manage', 'perencanaan.approve', 'laporan.export',
            ],
            'Verifikator Lapangan' => [
                'psn.view', 'pengendalian.manage', 'perencanaan.manage',
            ],
            'K/L Pelaksana' => [
                'psn.view', 'ro.manage', 'memo.manage',
            ],
            'Viewer Internal' => [
                'psn.view', 'audit.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }
    }
}
