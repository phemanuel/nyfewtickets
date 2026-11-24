<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\TicketCheckinLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Every entrance/exit transition is committed under a ticket row lock. */
class AdmissionService
{
    public function state(Ticket $ticket): string
    {
        if ($ticket->gate_state !== null) {
            return $ticket->gate_state;
        }
        // Tickets issued before this feature was installed.
        return $ticket->checked_in_at === null ? 'not_arrived' : 'inside';
    }

    public function admit(int $ticketId, int $staffId): Ticket
    {
        return $this->enter($ticketId, $staffId);
    }

    public function enter(int $ticketId, int $staffId): Ticket
    {
        return DB::transaction(function () use ($ticketId, $staffId) {
            $ticket = Ticket::query()->whereKey($ticketId)->lockForUpdate()->firstOrFail();
            $this->assertValid($ticket);
            $state = $this->state($ticket);

            if ($state === 'inside') {
                throw ValidationException::withMessages(['ticket' => 'ALREADY INSIDE: This ticket is currently admitted. Do not admit another person. Refer any dispute to a supervisor.']);
            }
            if (! in_array($state, ['not_arrived', 'outside'], true)) {
                throw ValidationException::withMessages(['ticket' => 'This ticket has an unrecognised entrance state. Ask a supervisor.']);
            }

            $reentry = $state === 'outside';
            $data = ['gate_state' => 'inside', 'last_entered_at' => now(), 'last_exited_at' => null];
            if ($ticket->checked_in_at === null) {
                $data['checked_in_at'] = now();
                $data['checked_in_by'] = $staffId;
            }
            $ticket->forceFill($data)->save();
            TicketCheckinLog::create([
                'ticket_id' => $ticket->id,
                'actor_id' => $staffId,
                'action' => $reentry ? 'reentered' : 'admitted',
            ]);
            return $ticket->load(['guest', 'event', 'category']);
        }, 3);
    }

    public function exit(int $ticketId, int $staffId): Ticket
    {
        return DB::transaction(function () use ($ticketId, $staffId) {
            $ticket = Ticket::query()->whereKey($ticketId)->lockForUpdate()->firstOrFail();
            $this->assertValid($ticket);
            if ($this->state($ticket) !== 'inside') {
                throw ValidationException::withMessages(['ticket' => 'Only guests currently INSIDE may be checked out.']);
            }
            $ticket->forceFill(['gate_state' => 'outside', 'last_exited_at' => now()])->save();
            TicketCheckinLog::create(['ticket_id' => $ticket->id, 'actor_id' => $staffId, 'action' => 'exited']);
            return $ticket;
        }, 3);
    }

    /** Supervisor correction; does not erase the first admission timestamp or audit history. */
    public function correctExit(int $ticketId, int $supervisorId, string $reason): Ticket
    {
        return DB::transaction(function () use ($ticketId, $supervisorId, $reason) {
            $ticket = Ticket::query()->whereKey($ticketId)->lockForUpdate()->firstOrFail();
            $this->assertValid($ticket);
            if ($this->state($ticket) !== 'inside') {
                throw ValidationException::withMessages(['ticket' => 'Only a ticket marked INSIDE can have a missing exit corrected.']);
            }
            $ticket->forceFill(['gate_state' => 'outside', 'last_exited_at' => now()])->save();
            TicketCheckinLog::create(['ticket_id' => $ticketId, 'actor_id' => $supervisorId, 'action' => 'exit_corrected', 'reason' => $reason]);
            return $ticket;
        }, 3);
    }

    /** Retain compatibility with the old check-in reversal feature, but do not destroy history. */
    public function reverse(int $ticketId, int $supervisorId, string $reason): Ticket
    {
        return DB::transaction(function () use ($ticketId, $supervisorId, $reason) {
            $ticket = Ticket::query()->whereKey($ticketId)->lockForUpdate()->firstOrFail();
            if ($this->state($ticket) !== 'inside') {
                throw ValidationException::withMessages(['ticket' => 'Only an active admission can be reversed.']);
            }
            // Preserve the existing Phase 4 meaning: undo a mistaken admission.
            // Physical exit and missing-exit correction use separate methods above.
            $ticket->forceFill([
                'gate_state' => 'not_arrived',
                'checked_in_at' => null,
                'checked_in_by' => null,
                'last_entered_at' => null,
                'last_exited_at' => null,
            ])->save();
            TicketCheckinLog::create(['ticket_id' => $ticketId, 'actor_id' => $supervisorId, 'action' => 'reversed', 'reason' => $reason]);
            return $ticket;
        }, 3);
    }

    private function assertValid(Ticket $ticket): void
    {
        if ($ticket->status !== 'issued' || $ticket->revoked_at !== null) {
            throw ValidationException::withMessages(['ticket' => 'Ticket is invalid or revoked. Entry and exit are blocked.']);
        }
    }
}
