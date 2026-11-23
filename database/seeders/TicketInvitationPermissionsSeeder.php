<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TicketInvitationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['tickets.view','tickets.issue','tickets.verify','tickets.revoke','tickets.import'];
        foreach ($names as $name) Permission::findOrCreate($name,'web');
        Role::findOrCreate('super-admin','web')->givePermissionTo($names);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
