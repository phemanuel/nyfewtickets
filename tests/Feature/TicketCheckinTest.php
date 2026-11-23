<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\Tickets\InvitationIssuer;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TicketInvitationPermissionsSeeder;
use Database\Seeders\TicketCheckinPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketCheckinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(TicketInvitationPermissionsSeeder::class);
        $this->seed(TicketCheckinPermissionsSeeder::class);
    }

    private function issueTicket(): Ticket
    {
        $event = Event::create(['title'=>'Grand Finale','slug'=>'grand-finale','starts_at'=>now()->addDays(3),'status'=>'published']);
        $category = TicketCategory::create(['event_id'=>$event->id,'name'=>'Special Guest','slug'=>'special-guest','type'=>'complimentary','price_kobo'=>0,'capacity'=>10,'is_active'=>true]);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        [$ticket] = app(InvitationIssuer::class)->issue($event,$category,['full_name'=>'Demo Guest'], $admin->id);
        return $ticket;
    }

    private function officer(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['admin.access', 'checkins.scan']);
        return $user;
    }

    public function test_officer_can_check_in_once_only(): void
    {
        $ticket = $this->issueTicket();
        $officer = $this->officer();
        $this->actingAs($officer)->post(route('admin.checkins.admit', $ticket))->assertRedirect(route('admin.checkins.show', $ticket));
        $this->assertNotNull($ticket->fresh()->checked_in_at);
        $this->assertDatabaseCount('ticket_checkin_logs', 1);
        $this->actingAs($officer)->from(route('admin.checkins.show', $ticket))->post(route('admin.checkins.admit', $ticket))->assertSessionHasErrors('ticket');
        $this->assertDatabaseCount('ticket_checkin_logs', 1);
    }

    public function test_revoked_ticket_is_rejected(): void
    {
        $ticket = $this->issueTicket();
        $ticket->update(['status' => 'revoked', 'revoked_at' => now()]);
        $this->actingAs($this->officer())->post(route('admin.checkins.admit', $ticket))->assertSessionHasErrors('ticket');
        $this->assertNull($ticket->fresh()->checked_in_at);
    }

    public function test_officer_cannot_reverse_but_super_admin_can(): void
    {
        $ticket = $this->issueTicket();
        $officer = $this->officer();
        $this->actingAs($officer)->post(route('admin.checkins.admit', $ticket));
        $this->actingAs($officer)->post(route('admin.checkins.reverse', $ticket), ['reason'=>'Wrong guest check-in'])->assertForbidden();
        $super = User::factory()->create();
        $super->assignRole('super-admin');
        $this->actingAs($super)->post(route('admin.checkins.reverse', $ticket), ['reason'=>'Accidental admission'])->assertRedirect();
        $this->assertNull($ticket->fresh()->checked_in_at);
        $this->assertDatabaseCount('ticket_checkin_logs', 2);
    }

    public function test_unauthenticated_and_unprivileged_users_cannot_access_gate(): void
    {
        $this->get(route('admin.checkins.index'))->assertRedirect();
        $this->actingAs(User::factory()->create())->get(route('admin.checkins.index'))->assertForbidden();
    }

    public function test_ticket_number_lookup_opens_preview_without_admitting(): void
    {
        $ticket = $this->issueTicket();
        $this->actingAs($this->officer())->post(route('admin.checkins.lookup'), ['reference'=>$ticket->number])
            ->assertRedirect(route('admin.checkins.show', $ticket));
        $this->assertNull($ticket->fresh()->checked_in_at);
    }
}
