<?php
namespace App\Services\Tickets;

use App\Mail\TicketReadyMail;
use App\Models\TicketOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TicketDeliveryService
{
    /** Repeated payment webhooks cannot schedule duplicate initial emails. */
    public function sendIfNeeded(TicketOrder $order): void
    {
        if ($order->status !== 'paid') return;
        try {
            $created = DB::table('ticket_order_notifications')->insertOrIgnore([
                'ticket_order_id'=>$order->id, 'queued_at'=>now(),
                'created_at'=>now(), 'updated_at'=>now(),
            ]);
            if ($created) $this->queueAccessEmail($order);
        } catch (\Throwable $e) {
            Log::error('NYFEW ticket email could not be queued', ['order_id'=>$order->id, 'error'=>$e->getMessage()]);
            // Payment must remain valid even if mail is unavailable. Admin can resend.
        }
    }

    /** Explicit admin resend or purchaser recovery; does not modify ticket QR codes. */
    public function queueAccessEmail(TicketOrder $order, int $minutes = 1440): void
    {
        abort_unless($order->status === 'paid', 403);
        $url = app(TicketAccessService::class)->createLink($order, $minutes);
        Mail::to($order->buyer_email)->queue(new TicketReadyMail(
            $order->buyer_name, $order->event->title, $order->reference, $url
        ));
    }
}
