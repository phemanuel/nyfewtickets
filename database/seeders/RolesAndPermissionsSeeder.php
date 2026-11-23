<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $names = ['admin.access','events.view','events.create','events.edit','events.delete','staff.view','staff.manage','tickets.issue','tickets.view','tickets.cancel','orders.view','checkins.scan','checkins.override','reports.view'];
        foreach ($names as $name) { Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']); }
        $super = Role::firstOrCreate(['name'=>'super-admin','guard_name'=>'web']);
        $super->syncPermissions($names);
        $manager = Role::firstOrCreate(['name'=>'ticket-manager','guard_name'=>'web']);
        $manager->syncPermissions(array_diff($names,['staff.manage','checkins.override']));
        $gate = Role::firstOrCreate(['name'=>'gate-officer','guard_name'=>'web']);
        $gate->syncPermissions(['admin.access','checkins.scan']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
