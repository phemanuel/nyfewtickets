<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TicketSalesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate(['name'=>'ticket-sales.view','guard_name'=>'web']);
        $role = Role::where('name','super-admin')->where('guard_name','web')->first();
        if ($role) $role->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
