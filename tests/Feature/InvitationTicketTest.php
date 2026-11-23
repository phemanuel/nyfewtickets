<?php
namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\Tickets\InvitationIssuer;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TicketInvitationPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InvitationTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(TicketInvitationPermissionsSeeder::class);
    }

    private function setupEvent(string $type='complimentary',int $capacity=2): array
    {
        $event = Event::create(['title'=>'NYFEW Grand Finale','slug'=>'nyfew-grand-finale','starts_at'=>now()->addDays(5),'status'=>'published']);
        $category = TicketCategory::create(['event_id'=>$event->id,'name'=>'Special Guest','slug'=>'special-guest','type'=>$type,'price_kobo'=>$type==='paid'?500000:0,'capacity'=>$capacity,'is_active'=>true]);
        return [$event,$category];
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        return $user;
    }

    public function test_super_admin_can_issue_unique_qr_ticket(): void
    {
        [$event,$category]=$this->setupEvent();
        $admin=$this->admin();
        [$ticket,$token]=app(InvitationIssuer::class)->issue($event,$category,['full_name'=>'Guest Example','email'=>'guest@example.test'],$admin->id);
        $this->assertSame('invitation',$ticket->kind);
        $this->assertSame(hash('sha256',$token),$ticket->token_hash);
        $this->assertNotSame($token,$ticket->token_hash);
        $this->assertDatabaseCount('tickets',1);
        $this->get(route('tickets.verify',['token'=>$token]))->assertOk()->assertDontSee('Guest Example');
        $this->actingAs($admin)->get(route('tickets.verify',['token'=>$token]))->assertOk()->assertSee('Guest Example');
    }

    public function test_paid_category_cannot_be_issued_as_free_invitation(): void
    {
        [$event,$category]=$this->setupEvent('paid');
        $this->expectException(ValidationException::class);
        app(InvitationIssuer::class)->issue($event,$category,['full_name'=>'Guest'], $this->admin()->id);
    }

    public function test_capacity_cannot_be_exceeded(): void
    {
        [$event,$category]=$this->setupEvent('complimentary',1);
        $admin=$this->admin();
        app(InvitationIssuer::class)->issue($event,$category,['full_name'=>'First Guest','email'=>'one@example.test'],$admin->id);
        try {
            app(InvitationIssuer::class)->issue($event,$category,['full_name'=>'Second Guest','email'=>'two@example.test'],$admin->id);
            $this->fail('Expected capacity validation error');
        } catch (ValidationException $e) {
            $this->assertDatabaseCount('tickets',1);
        }
    }

    public function test_duplicate_contact_cannot_receive_second_invitation(): void
    {
        [$event,$category]=$this->setupEvent();
        $admin=$this->admin();
        app(InvitationIssuer::class)->issue($event,$category,['full_name'=>'First','email'=>'duplicate@example.test'],$admin->id);
        $this->expectException(ValidationException::class);
        app(InvitationIssuer::class)->issue($event,$category,['full_name'=>'Duplicate','email'=>'duplicate@example.test'],$admin->id);
    }

    public function test_guest_cannot_open_admin_invitations(): void
    {
        $this->get(route('admin.invitations.index'))->assertRedirect();
        $ordinary=User::factory()->create();
        $this->actingAs($ordinary)->get(route('admin.invitations.index'))->assertForbidden();
    }

    public function test_revoked_ticket_no_longer_verifies_as_valid(): void
    {
        [$event,$category]=$this->setupEvent();
        $admin=$this->admin();
        [$ticket,$token]=app(InvitationIssuer::class)->issue($event,$category,['full_name'=>'Revoked Guest'],$admin->id);
        $this->actingAs($admin)->post(route('admin.invitations.revoke',$ticket))->assertRedirect();
        $this->get(route('tickets.verify',['token'=>$token]))->assertOk()->assertSee('not valid for entry');
    }
}
