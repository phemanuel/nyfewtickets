<?php
namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_homepage_is_accessible(): void { $this->get('/')->assertOk(); }
    public function test_admin_dashboard_requires_authentication(): void { $this->get('/admin')->assertRedirect('/login'); }
    public function test_unprivileged_user_is_denied_admin_access(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
    public function test_super_admin_can_access_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        $this->actingAs($user)->get('/admin')->assertOk();
    }
}
