<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCheckinLog;
use App\Services\Tickets\AdmissionService;
use Illuminate\Http\Request;

class TicketCheckinController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::query()->orderByDesc('starts_at')->get(['id', 'title']);
        $eventId = $request->integer('event_id') ?: null;
        $summary = Ticket::query()->when($eventId, fn ($q) => $q->where('event_id', $eventId));
        $issued = (clone $summary)->where('status', 'issued')->count();
        $checkedIn = (clone $summary)->where('status', 'issued')->whereNotNull('checked_in_at')->count();
        $history = TicketCheckinLog::query()->with(['ticket.guest', 'ticket.event', 'actor'])
            ->when($eventId, fn ($q) => $q->whereHas('ticket', fn ($q) => $q->where('event_id', $eventId)))
            ->latest('id')->paginate(20)->withQueryString();

        return view('admin.checkins.index', compact('events', 'eventId', 'issued', 'checkedIn', 'history'));
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:500'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
        ]);
        $reference = trim($data['reference']);
        $token = null;

        // QR codes contain a URL: extract token only from an exact /ticket/verify/{token} path.
        if (preg_match('~^https?://~i', $reference)) {
            $path = parse_url($reference, PHP_URL_PATH);
            if (! is_string($path) || ! preg_match('~^/ticket/verify/([A-Za-z0-9]{80})/?$~D', $path, $matches)) {
                return back()->withErrors(['reference' => 'This is not a recognised NYFEW QR ticket URL.'])->withInput();
            }
            $token = $matches[1];
        } elseif (preg_match('/^[A-Za-z0-9]{80}$/D', $reference)) {
            $token = $reference;
        }

        $ticket = Ticket::query()->with(['guest', 'category', 'event'])
            ->when(! empty($data['event_id']), fn ($q) => $q->where('event_id', $data['event_id']))
            ->when($token !== null,
                fn ($q) => $q->where('token_hash', hash('sha256', $token)),
                fn ($q) => $q->where('number', $reference))
            ->first();

        if (! $ticket) {
            return back()->withErrors(['reference' => 'No matching ticket was found.'])->withInput();
        }

        return redirect()->route('admin.checkins.show', $ticket);
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['guest', 'category', 'event', 'issuer']);
        $history = TicketCheckinLog::query()->with('actor')->where('ticket_id', $ticket->id)->latest('id')->get();
        return view('admin.checkins.show', compact('ticket', 'history'));
    }

    public function admit(Request $request, Ticket $ticket, AdmissionService $service)
    {
        $service->admit($ticket->id, $request->user()->id);
        return redirect()->route('admin.checkins.show', $ticket)->with('success', 'Guest checked in successfully.');
    }

    public function reverse(Request $request, Ticket $ticket, AdmissionService $service)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:1000']]);
        $service->reverse($ticket->id, $request->user()->id, $data['reason']);
        return redirect()->route('admin.checkins.show', $ticket)->with('success', 'Check-in reversed. The action was recorded.');
    }
}
