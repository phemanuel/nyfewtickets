<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TicketCheckinPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['checkins.scan', 'checkins.override'] as $name) {
            Permission::findOrCreate($name, 'web');
        }
        Role::findOrCreate('super-admin', 'web')->givePermissionTo(['checkins.scan', 'checkins.override']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
