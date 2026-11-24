<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GateReentryPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin.access', 'checkins.scan', 'checkins.override', 'checkins.exit'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $gate = Role::findOrCreate('gate-officer', 'web');
        $gate->givePermissionTo(['admin.access', 'checkins.scan', 'checkins.exit']);
        Role::findOrCreate('super-admin', 'web')->givePermissionTo([
            'admin.access', 'checkins.scan', 'checkins.override', 'checkins.exit',
        ]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
