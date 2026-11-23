<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessManagementPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';
        foreach (['admin.access','staff.view','staff.manage','roles.view','roles.manage'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        // Add to the existing super-admin role; do not reset other roles or custom permissions.
        $super = Role::findOrCreate('super-admin', $guard);
        $super->givePermissionTo(['admin.access','staff.view','staff.manage','roles.view','roles.manage']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
