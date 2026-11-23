<?php
namespace App\Services\Tickets;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketGuest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationIssuer
{
    /** A lock on the event row serialises issuance against this event's inventory. */
    public function issue(Event $event, TicketCategory $category, array $guestData, int $issuerId): array
    {
        return DB::transaction(function () use ($event, $category, $guestData, $issuerId) {
            Event::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();
            $category = TicketCategory::query()->where('event_id', $event->id)->findOrFail($category->id);
            if ($category->type !== 'complimentary' || ! $category->is_active) {
                throw ValidationException::withMessages(['category_id'=>'Choose an active complimentary category. Paid categories require verified payment.']);
            }
            $issued = Ticket::query()->where('ticket_category_id', $category->id)->where('status', 'issued')->count();
            if ($issued >= $category->capacity) {
                throw ValidationException::withMessages(['category_id'=>'This category has reached its ticket allocation.']);
            }
            // Do not issue the same event/category invitation twice to the same contact.
            $duplicate = Ticket::query()->where('event_id', $event->id)->where('ticket_category_id', $category->id)
                ->where('status', 'issued')->whereHas('guest', function ($q) use ($guestData) {
                    if (!empty($guestData['email'])) $q->where('email', mb_strtolower(trim($guestData['email'])));
                    elseif (!empty($guestData['phone'])) $q->where('phone', trim($guestData['phone']));
                    else $q->whereRaw('1 = 0');
                })->exists();
            if ($duplicate) throw ValidationException::withMessages(['email'=>'This contact already has an active invitation for this category.']);

            $guest = TicketGuest::create($guestData);
            $token = Str::random(80);
            $ticket = Ticket::create([
                'event_id'=>$event->id, 'ticket_category_id'=>$category->id, 'ticket_guest_id'=>$guest->id,
                'issued_by'=>$issuerId, 'number'=>'NYF-'.str_pad((string)$event->id, 3, '0', STR_PAD_LEFT).'-'.strtoupper(Str::random(12)),
                'token_hash'=>hash('sha256',$token), 'kind'=>'invitation', 'status'=>'issued', 'issued_at'=>now(),
            ]);
            return [$ticket->load('guest','category','event'), $token];
        }, 3);
    }
}
