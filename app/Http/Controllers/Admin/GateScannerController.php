<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCheckinLog;
use App\Services\Tickets\AdmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GateScannerController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::query()->orderByDesc('starts_at')->get(['id', 'title']);
        $selectedEvent = $request->integer('event_id') ?: null;
        $query = Ticket::query()->where('status', 'issued')->whereNull('revoked_at')
            ->when($selectedEvent, fn ($q) => $q->where('event_id', $selectedEvent));
        $total = (clone $query)->count();
        $inside = (clone $query)->where('gate_state', 'inside')->count();
        $outside = (clone $query)->where('gate_state', 'outside')->count();
        $history = TicketCheckinLog::query()->with(['ticket.guest', 'ticket.event', 'actor'])
            ->when($selectedEvent, fn ($q) => $q->whereHas('ticket', fn ($t) => $t->where('event_id', $selectedEvent)))
            ->latest('id')->limit(12)->get();
        return view('admin.gate.index', compact('events', 'selectedEvent', 'total', 'inside', 'outside', 'history'));
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:500'],
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ]);
        $reference = trim($data['reference']);
        $token = null;
        if (preg_match('~^https?://~i', $reference)) {
            $path = parse_url($reference, PHP_URL_PATH);
            if (!is_string($path) || !preg_match('~^/ticket/verify/([A-Za-z0-9]{80})/?$~D', $path, $match)) {
                return back()->withErrors(['reference' => 'Unrecognised ticket QR.'])->withInput();
            }
            $token = $match[1];
        } elseif (preg_match('/^[A-Za-z0-9]{80}$/D', $reference)) {
            $token = $reference;
        }
        $ticket = Ticket::query()->where('event_id', $data['event_id'])
            ->when($token !== null,
                fn ($q) => $q->where('token_hash', hash('sha256', $token)),
                fn ($q) => $q->where('number', $reference))
            ->first();
        if (!$ticket) {
            return back()->withErrors(['reference' => 'Ticket not found for the selected event.'])->withInput();
        }
        return redirect()->route('admin.gate.show', ['ticket' => $ticket, 'event_id' => $data['event_id']]);
    }

    public function show(Request $request, Ticket $ticket, AdmissionService $service)
    {
        $ticket->load(['guest', 'category', 'event']);
        $state = $service->state($ticket);
        $history = TicketCheckinLog::query()->with('actor')->where('ticket_id', $ticket->id)->latest('id')->limit(15)->get();
        return view('admin.gate.show', compact('ticket', 'state', 'history'));
    }

    public function photo(Ticket $ticket)
    {
        $path = $ticket->guest?->photo_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404);
        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function enter(Request $request, Ticket $ticket, AdmissionService $service)
    {
        $before = $service->state($ticket);
        $service->enter($ticket->id, $request->user()->id);
        return redirect()->route('admin.gate.show', $ticket)->with('success', $before === 'outside' ? 'Re-entry recorded.' : 'Guest admitted successfully.');
    }

    public function exit(Request $request, Ticket $ticket, AdmissionService $service)
    {
        $service->exit($ticket->id, $request->user()->id);
        return redirect()->route('admin.gate.show', $ticket)->with('success', 'Guest exit recorded. Re-entry is now available.');
    }

    public function correctExit(Request $request, Ticket $ticket, AdmissionService $service)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:1000']]);
        $service->correctExit($ticket->id, $request->user()->id, $data['reason']);
        return redirect()->route('admin.gate.show', $ticket)->with('success', 'Supervisor correction recorded. Verify identity before re-entry.');
    }
}
