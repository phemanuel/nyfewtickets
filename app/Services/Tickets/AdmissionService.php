<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\TicketCheckinLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** All admission state transitions must pass through this service. */
class AdmissionService
{
    public function admit(int $ticketId, int $staffId): Ticket
    {
        return DB::transaction(function () use ($ticketId, $staffId) {
            $ticket = Ticket::query()->whereKey($ticketId)->lockForUpdate()->firstOrFail();

            if ($ticket->status !== 'issued' || $ticket->revoked_at !== null) {
                throw ValidationException::withMessages(['ticket' => 'This ticket is cancelled or invalid for entry.']);
            }
            if ($ticket->checked_in_at !== null) {
                throw ValidationException::withMessages(['ticket' => 'Already checked in at '.$ticket->checked_in_at->format('d M Y, H:i').'.']);
            }

            $ticket->forceFill(['checked_in_at' => now(), 'checked_in_by' => $staffId])->save();
            TicketCheckinLog::create(['ticket_id' => $ticket->id, 'actor_id' => $staffId, 'action' => 'admitted']);

            return $ticket->load(['guest', 'event', 'category']);
        }, 3);
    }

    public function reverse(int $ticketId, int $supervisorId, string $reason): Ticket
    {
        return DB::transaction(function () use ($ticketId, $supervisorId, $reason) {
            $ticket = Ticket::query()->whereKey($ticketId)->lockForUpdate()->firstOrFail();
            if ($ticket->checked_in_at === null) {
                throw ValidationException::withMessages(['ticket' => 'This ticket has no active check-in to reverse.']);
            }

            $ticket->forceFill(['checked_in_at' => null, 'checked_in_by' => null])->save();
            TicketCheckinLog::create(['ticket_id' => $ticket->id, 'actor_id' => $supervisorId, 'action' => 'reversed', 'reason' => $reason]);

            return $ticket;
        }, 3);
    }
}
