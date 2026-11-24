<?php
namespace App\Services\Tickets;

use App\Models\{Event,Ticket,TicketCategory,TicketGuest,TicketOrder,TicketOrderAttendee};
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketSaleService
{
    /** Returns order + secret to be held in the purchaser's server-side session. */
    public function reserve(Event $event, array $buyer, array $attendees): array
    {
        return DB::transaction(function () use ($event, $buyer, $attendees) {
            $event = Event::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();
            if ($event->status !== 'published' || $event->starts_at->isPast()) {
                throw ValidationException::withMessages(['event' => 'Tickets are not currently on sale for this event.']);
            }
            $grouped = collect($attendees)->groupBy('category_id');
            $categories = TicketCategory::where('event_id', $event->id)->whereIn('id', $grouped->keys())->get()->keyBy('id');
            if ($grouped->count() !== $categories->count()) {
                throw ValidationException::withMessages(['attendees' => 'An invalid ticket category was selected.']);
            }
            $now = now();
            foreach ($grouped as $categoryId => $entries) {
                $category = $categories->get((int) $categoryId);
                if (!$category || $category->type !== 'paid' || !$category->is_active || $category->price_kobo < 100 ||
                    ($category->sales_start_at && $category->sales_start_at->gt($now)) ||
                    ($category->sales_end_at && $category->sales_end_at->lt($now))) {
                    throw ValidationException::withMessages(['attendees' => 'A selected ticket category is not available for sale.']);
                }
                $issued = Ticket::where('ticket_category_id', $categoryId)->where('status', 'issued')->count();
                $reserved = TicketOrderAttendee::where('ticket_category_id', $categoryId)
                    ->whereHas('order', fn ($q) => $q->where('status', 'pending')->where('expires_at', '>', $now))->count();
                if ($issued + $reserved + $entries->count() > $category->capacity) {
                    throw ValidationException::withMessages(['attendees' => 'Insufficient remaining tickets in '.$category->name.'.']);
                }
            }
            $access = Str::random(64);
            $order = TicketOrder::create([
                'event_id'=>$event->id,'reference'=>'NYFT-'.strtoupper(Str::random(24)),
                'buyer_name'=>$buyer['name'],'buyer_email'=>strtolower(trim($buyer['email'])),
                'buyer_phone'=>$buyer['phone'] ?? null,'access_hash'=>hash('sha256', $access),
                'amount_kobo'=>collect($attendees)->sum(fn ($a) => $categories->get((int) $a['category_id'])->price_kobo),
                'currency'=>'NGN','status'=>'pending',
                'expires_at'=>$now->copy()->addMinutes(config('nyfew_paystack.reservation_minutes', 20)),
            ]);
            foreach ($attendees as $person) {
                $order->attendees()->create([
                    'ticket_category_id'=>$person['category_id'],
                    'full_name'=>$person['full_name'],'email'=>$person['email'] ?? null,
                    'phone'=>$person['phone'] ?? null,
                    'unit_price_kobo'=>$categories->get((int) $person['category_id'])->price_kobo,
                ]);
            }
            return [$order, $access];
        }, 3);
    }

    /** Safe to call repeatedly from both webhook and customer callback. */
    public function confirm(string $reference, array $verified): TicketOrder
    {
        $confirmed = DB::transaction(function () use ($reference, $verified) {
            // Reference is unique; lock associated event to serialize sale finalisation against reservations.
            $order = TicketOrder::where('reference', $reference)->firstOrFail();
            Event::whereKey($order->event_id)->lockForUpdate()->firstOrFail();
            $order = TicketOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->status === 'paid') return $order;
            if ($order->status === 'paid_unfulfilled') return $order;

            if (($verified['reference'] ?? null) !== $order->reference ||
                ($verified['status'] ?? null) !== 'success' ||
                ($verified['currency'] ?? null) !== 'NGN' ||
                (int)($verified['amount'] ?? -1) !== $order->amount_kobo ||
                strtolower((string) data_get($verified, 'customer.email', '')) !== strtolower($order->buyer_email)) {
                throw ValidationException::withMessages(['payment' => 'Payment details do not match this order.']);
            }
            $order->load('attendees');
            foreach ($order->attendees->groupBy('ticket_category_id') as $categoryId => $entries) {
                $category = TicketCategory::whereKey($categoryId)->firstOrFail();
                $issued = Ticket::where('ticket_category_id', $categoryId)->where('status', 'issued')->count();
                // Exclude this order's reservation, because it is being converted to issued tickets.
                $otherReservations = TicketOrderAttendee::where('ticket_category_id', $categoryId)
                    ->where('ticket_order_id', '!=', $order->id)
                    ->whereHas('order', fn ($q) => $q->where('status','pending')->where('expires_at','>',now()))->count();
                if ($issued + $otherReservations + $entries->count() > $category->capacity) {
                    $order->update(['status'=>'paid_unfulfilled','paid_at'=>now(),
                        'paystack_transaction_id'=>(string)($verified['id'] ?? ''),
                        'payment_issue'=>'Payment succeeded after reserved inventory was no longer available. Manual refund or fulfillment required.']);
                    return $order;
                }
            }
            foreach ($order->attendees as $attendee) {
                if ($attendee->ticket_id) continue;
                $guest = TicketGuest::create([
                    'full_name'=>$attendee->full_name,'email'=>$attendee->email,'phone'=>$attendee->phone,
                ]);
                $token = Str::random(80);
                $ticket = Ticket::create([
                    'event_id'=>$order->event_id,'ticket_category_id'=>$attendee->ticket_category_id,
                    'ticket_guest_id'=>$guest->id,'issued_by'=>null,
                    'number'=>'NYF-'.str_pad((string)$order->event_id, 3, '0', STR_PAD_LEFT).'-'.strtoupper(Str::random(12)),
                    'token_hash'=>hash('sha256',$token),'kind'=>'paid','status'=>'issued',
                    'issued_at'=>now(),
                ]);
                $ticket->forceFill(['qr_token_encrypted'=>Crypt::encryptString($token)])->save();
                $attendee->update(['ticket_id'=>$ticket->id]);
            }
            $order->update(['status'=>'paid','paid_at'=>now(),
                'paystack_transaction_id'=>(string)($verified['id'] ?? '')]);
            return $order;
        }, 3);
        if ($confirmed->status === 'paid') {
            app(TicketDeliveryService::class)->sendIfNeeded($confirmed);
        }
        return $confirmed;
    }
}
