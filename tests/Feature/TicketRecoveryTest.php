<?php
namespace Tests\Feature;

use App\Mail\TicketReadyMail;
use App\Models\Event;
use App\Models\TicketAccessLink;
use App\Models\TicketOrder;
use App\Services\Tickets\TicketAccessService;
use App\Services\Tickets\TicketDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class TicketRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $status = 'paid'): TicketOrder
    {
        $event = Event::create(['title'=>'Grand Finale', 'slug'=>'grand-finale', 'starts_at'=>now()->addDays(7), 'status'=>'published']);
        return TicketOrder::create([
            'event_id'=>$event->id,'reference'=>'NYFT-'.strtoupper(\Illuminate\Support\Str::random(24)),
            'buyer_name'=>'Test Buyer','buyer_email'=>'buyer@example.com', 'access_hash'=>hash('sha256', 'buyer-session-secret'),
            'amount_kobo'=>100000,'currency'=>'NGN','status'=>$status,'expires_at'=>now()->addHour(),
        ]);
    }

    public function test_retrieval_form_is_public(): void
    {
        $this->get(route('ticket-recovery.index'))->assertOk();
    }

    public function test_email_recovery_is_generic_and_only_queues_for_matching_paid_order(): void
    {
        Mail::fake();
        $order = $this->order();
        RateLimiter::clear('nyfew-recovery:'.hash('sha256','buyer@example.com'));
        $this->post(route('ticket-recovery.request'), ['reference'=>$order->reference,'email'=>'buyer@example.com'])->assertRedirect();
        Mail::assertQueued(TicketReadyMail::class, 1);
        $this->post(route('ticket-recovery.request'), ['reference'=>'NOT-A-REAL-ORDER','email'=>'unknown@example.com'])->assertRedirect();
        Mail::assertQueued(TicketReadyMail::class, 1);
    }

    public function test_link_is_reusable_for_24_hours_and_grants_session_access(): void
    {
        $order = $this->order();
        $url = app(TicketAccessService::class)->createLink($order);
        $token = basename(parse_url($url, PHP_URL_PATH));
        $this->get($url)->assertOk();
        $this->post(route('ticket-recovery.consume', $token))->assertRedirect(route('sales.receipt', $order->reference));
        $this->get(route('sales.receipt', $order->reference))->assertOk();
        $this->get($url)->assertOk();
        $this->post(route('ticket-recovery.consume', $token))->assertRedirect(route('sales.receipt', $order->reference));
        $this->assertNull(TicketAccessLink::where('token_hash', hash('sha256', $token))->firstOrFail()->used_at);
    }

    public function test_legacy_used_link_remains_invalid(): void
    {
        $order = $this->order();
        $url = app(TicketAccessService::class)->createLink($order);
        $token = basename(parse_url($url, PHP_URL_PATH));
        TicketAccessLink::where('token_hash', hash('sha256', $token))->update(['used_at' => now()]);
        $this->get($url)->assertStatus(410)->assertSee('This access link is no longer valid');
        $this->post(route('ticket-recovery.consume', $token))->assertStatus(410);
    }

    public function test_unpaid_order_cannot_issue_access_link_email(): void
    {
        Mail::fake();
        $order = $this->order('pending');
        $this->post(route('ticket-recovery.request'), ['reference'=>$order->reference,'email'=>'buyer@example.com'])->assertRedirect();
        Mail::assertNothingQueued();
    }

    public function test_expired_link_is_rejected(): void
    {
        $order = $this->order();
        $url = app(TicketAccessService::class)->createLink($order);
        $this->travel(24)->hours();
        $this->travel(1)->seconds();
        $this->get($url)->assertStatus(410)->assertSee('This access link is no longer valid');
    }
}
