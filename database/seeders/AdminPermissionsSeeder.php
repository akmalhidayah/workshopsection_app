<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class AdminPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = config('admin_permissions.permissions', []);

        foreach ($permissions as $key => $label) {
            Permission::updateOrCreate(
                ['key' => $key],
                ['label' => $label]
            );

            if ($key !== 'admin.access_control') {
                RolePermission::updateOrCreate(
                    ['role' => 'admin', 'permission_key' => $key],
                    []
                );
            }
        }
    }
}
