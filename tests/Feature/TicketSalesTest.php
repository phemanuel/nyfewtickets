<?php
namespace Tests\Feature;

use App\Models\{Event,Ticket,TicketCategory,TicketOrder};
use App\Services\Tickets\TicketSaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketSalesTest extends TestCase
{
    use RefreshDatabase;

    private function sample(): array
    {
        $event = Event::create(['title'=>'Test Finale','slug'=>'test-finale','starts_at'=>now()->addDays(3),'status'=>'published']);
        $category = TicketCategory::create(['event_id'=>$event->id,'name'=>'VIP','slug'=>'vip','type'=>'paid','price_kobo'=>500000,'capacity'=>2,'is_active'=>true]);
        return [$event,$category];
    }

    private function buyer(): array { return ['name'=>'Test Buyer','email'=>'buyer@example.com']; }
    private function attendees(TicketCategory $category): array { return [['category_id'=>$category->id,'full_name'=>'First Guest','email'=>'guest@example.com']]; }

    public function test_checkout_requires_enabled_sales(): void
    {
        [$event,$category] = $this->sample();
        config(['nyfew_paystack.enabled'=>false]);
        $this->post(route('sales.checkout',$event), ['buyer'=>$this->buyer(),'attendees'=>$this->attendees($category)])->assertStatus(503);
    }

    public function test_paid_category_reservation_and_verified_issuance_is_idempotent(): void
    {
        [$event,$category] = $this->sample();
        $service = app(TicketSaleService::class);
        [$order] = $service->reserve($event,$this->buyer(),$this->attendees($category));
        $verified = ['reference'=>$order->reference,'status'=>'success','currency'=>'NGN','amount'=>500000,'id'=>12345,'customer'=>['email'=>'buyer@example.com']];
        $service->confirm($order->reference,$verified);
        $service->confirm($order->reference,$verified);
        $this->assertEquals(1, Ticket::where('kind','paid')->count());
        $this->assertEquals('paid',$order->fresh()->status);
        $this->assertNotNull(Ticket::first()->qr_token_encrypted);
    }

    public function test_payment_amount_mismatch_does_not_issue_tickets(): void
    {
        [$event,$category] = $this->sample();
        [$order] = app(TicketSaleService::class)->reserve($event,$this->buyer(),$this->attendees($category));
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try { app(TicketSaleService::class)->confirm($order->reference,['reference'=>$order->reference,'status'=>'success','amount'=>10,'currency'=>'NGN','customer'=>['email'=>'buyer@example.com']]); }
        finally { $this->assertSame(0,Ticket::count()); }
    }

    public function test_pending_reservations_limit_inventory(): void
    {
        [$event,$category] = $this->sample();
        $service = app(TicketSaleService::class);
        $service->reserve($event,$this->buyer(),$this->attendees($category));
        $service->reserve($event,$this->buyer(),$this->attendees($category));
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->reserve($event,$this->buyer(),$this->attendees($category));
    }

    public function test_unauthorised_receipt_access_is_forbidden(): void
    {
        [$event,$category] = $this->sample();
        [$order] = app(TicketSaleService::class)->reserve($event,$this->buyer(),$this->attendees($category));
        $this->get(route('sales.receipt', $order->reference))->assertForbidden();
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        config(['nyfew_paystack.secret_key'=>'sk_test_dummy']);
        $this->postJson(route('sales.webhook'),['event'=>'charge.success'],['x-paystack-signature'=>'invalid'])->assertUnauthorized();
    }
}
