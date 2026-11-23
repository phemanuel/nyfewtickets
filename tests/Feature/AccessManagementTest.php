<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AccessManagementPermissionsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(AccessManagementPermissionsSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        return $user;
    }

    public function test_staff_directory_requires_admin_authentication(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect();
    }

    public function test_super_admin_can_create_staff_and_assign_role(): void
    {
        $this->actingAs($this->superAdmin());
        $role = Role::findByName('gate-officer', 'web');
        $this->post(route('admin.users.store'), [
            'name' => 'Gate Officer Example',
            'email' => 'gate@example.test',
            'password' => 'CorrectHorseBattery!234',
            'password_confirmation' => 'CorrectHorseBattery!234',
            'roles' => [$role->id],
        ])->assertRedirect(route('admin.users.index'));
        $this->assertTrue(User::where('email','gate@example.test')->firstOrFail()->hasRole('gate-officer'));
    }

    public function test_super_admin_can_create_role_with_permissions(): void
    {
        $this->actingAs($this->superAdmin());
        $permission = \Spatie\Permission\Models\Permission::findByName('events.view', 'web');
        $this->post(route('admin.roles.store'), [
            'name' => 'event-assistant',
            'permissions' => [$permission->id],
        ])->assertRedirect(route('admin.roles.index'));
        $this->assertTrue(Role::findByName('event-assistant', 'web')->hasPermissionTo('events.view'));
    }

    public function test_non_super_staff_cannot_create_roles_or_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('ticket-manager');
        $this->actingAs($user);
        $this->get(route('admin.users.create'))->assertForbidden();
        $this->get(route('admin.roles.create'))->assertForbidden();
    }

    public function test_last_super_admin_cannot_lose_role(): void
    {
        $super = $this->superAdmin();
        $gate = Role::findByName('gate-officer', 'web');
        $this->actingAs($super)->put(route('admin.users.update',$super), [
            'name' => $super->name,
            'email' => $super->email,
            'roles' => [$gate->id],
        ])->assertSessionHasErrors('roles');
        $this->assertTrue($super->fresh()->hasRole('super-admin'));
    }

    public function test_super_admin_role_cannot_be_changed_or_deleted(): void
    {
        $this->actingAs($this->superAdmin());
        $role = Role::findByName('super-admin', 'web');
        $this->put(route('admin.roles.update',$role), ['name'=>'changed'])->assertForbidden();
        $this->delete(route('admin.roles.destroy',$role))->assertForbidden();
    }
}
