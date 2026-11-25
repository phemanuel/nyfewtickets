<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TicketGuestReportsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');
        foreach (['ticket-guests.view', 'ticket-reports.view', 'ticket-reports.export'] as $name) {
            Permission::findOrCreate($name, $guard);
        }
        $super = Role::findOrCreate('super-admin', $guard);
        $super->givePermissionTo(['ticket-guests.view', 'ticket-reports.view', 'ticket-reports.export']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
