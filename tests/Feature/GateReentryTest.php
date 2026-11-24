<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\Tickets\AdmissionService;
use App\Services\Tickets\InvitationIssuer;
use Database\Seeders\GateReentryPermissionsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TicketCheckinPermissionsSeeder;
use Database\Seeders\TicketInvitationPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GateReentryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(TicketInvitationPermissionsSeeder::class);
        $this->seed(TicketCheckinPermissionsSeeder::class);
        $this->seed(GateReentryPermissionsSeeder::class);
    }

    private function ticket(): Ticket
    {
        $event = Event::create(['title'=>'Gate Test','slug'=>'gate-test','starts_at'=>now()->addDay(),'status'=>'published']);
        $category = TicketCategory::create(['event_id'=>$event->id,'name'=>'Guest','slug'=>'guest','type'=>'complimentary','price_kobo'=>0,'capacity'=>10,'is_active'=>true]);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        [$ticket] = app(InvitationIssuer::class)->issue($event, $category, ['full_name'=>'Gate Test Guest'], $admin->id);
        return $ticket;
    }

    private function gate(): User
    {
        $user = User::factory()->create();
        $user->assignRole('gate-officer');
        return $user;
    }

    public function test_first_entry_exit_and_reentry_preserve_first_admission(): void
    {
        $ticket = $this->ticket();
        $gate = $this->gate();
        $service = app(AdmissionService::class);
        $service->enter($ticket->id, $gate->id);
        $first = $ticket->fresh()->checked_in_at;
        $this->assertNotNull($first);
        $this->assertSame('inside', $ticket->fresh()->gate_state);
        $service->exit($ticket->id, $gate->id);
        $this->assertSame('outside', $ticket->fresh()->gate_state);
        $service->enter($ticket->id, $gate->id);
        $this->assertSame('inside', $ticket->fresh()->gate_state);
        $this->assertSame($first->toDateTimeString(), $ticket->fresh()->checked_in_at->toDateTimeString());
        $this->assertSame(['admitted', 'exited', 'reentered'], \App\Models\TicketCheckinLog::query()->orderBy('id')->pluck('action')->all());
    }

    public function test_duplicate_entry_is_rejected_without_extra_log(): void
    {
        $ticket = $this->ticket();
        $gate = $this->gate();
        $service = app(AdmissionService::class);
        $service->enter($ticket->id, $gate->id);
        try {
            $service->enter($ticket->id, $gate->id);
            $this->fail('Expected duplicate admission to be blocked');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('ticket', $e->errors());
        }
        $this->assertDatabaseCount('ticket_checkin_logs', 1);
    }

    public function test_gate_officer_can_use_gate_but_cannot_correct_exit(): void
    {
        $ticket = $this->ticket();
        $gate = $this->gate();
        $this->actingAs($gate)->get(route('admin.gate.index'))->assertOk();
        $this->actingAs($gate)->post(route('admin.gate.enter', $ticket))->assertRedirect();
        $this->actingAs($gate)->post(route('admin.gate.correct-exit', $ticket), ['reason' => 'Guest forgot to scan exit'])->assertForbidden();
        $this->actingAs($gate)->post(route('admin.gate.exit', $ticket))->assertRedirect();
        $this->assertSame('outside', $ticket->fresh()->gate_state);
    }

    public function test_supervisor_can_correct_missing_exit_with_reason(): void
    {
        $ticket = $this->ticket();
        $gate = $this->gate();
        $super = User::factory()->create();
        $super->assignRole('super-admin');
        app(AdmissionService::class)->enter($ticket->id, $gate->id);
        $this->actingAs($super)->post(route('admin.gate.correct-exit', $ticket), ['reason' => 'Guest wristband checked at gate'])->assertRedirect();
        $this->assertSame('outside', $ticket->fresh()->gate_state);
        $this->assertDatabaseHas('ticket_checkin_logs', ['ticket_id' => $ticket->id, 'action'=>'exit_corrected']);
    }

    public function test_unprivileged_user_is_denied_gate(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.gate.index'))->assertForbidden();
    }
}
