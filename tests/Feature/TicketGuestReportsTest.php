<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TicketGuestReportsPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketGuestReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(TicketGuestReportsPermissionsSeeder::class);
    }

    public function test_guest_directory_requires_login(): void
    {
        $this->get(route('admin.guest-reports.index'))->assertRedirect();
    }

    public function test_unprivileged_user_cannot_view_reports_or_export(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.guest-reports.reports'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.guest-reports.export-guests'))->assertForbidden();
    }

    public function test_super_admin_can_view_reports_and_guest_directory(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        $this->actingAs($user)->get(route('admin.guest-reports.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.guest-reports.reports'))->assertOk();
    }
}
