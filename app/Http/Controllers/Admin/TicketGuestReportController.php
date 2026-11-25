<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketCheckinLog;
use App\Models\TicketOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketGuestReportController extends Controller
{
    private function filters(Request $request): array
    {
        return $request->validate([
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'q' => ['nullable', 'string', 'max:150'],
            'kind' => ['nullable', 'in:paid,invitation'],
            'presence' => ['nullable', 'in:not_arrived,inside,outside'],
            'status' => ['nullable', 'in:issued,revoked'],
        ]);
    }

    private function events()
    {
        return Event::query()->orderByDesc('starts_at')->get(['id', 'title']);
    }

    private function validTickets(?int $eventId): Builder
    {
        return Ticket::query()->when($eventId, fn (Builder $q) => $q->where('event_id', $eventId))
            ->where('status', 'issued')->whereNull('revoked_at');
    }

    private function guestQuery(array $filters): Builder
    {
        $q = Ticket::query()->with(['event:id,title', 'category:id,name', 'guest:id,full_name,email,phone,organisation']);
        if (!empty($filters['event_id'])) $q->where('event_id', (int) $filters['event_id']);
        if (!empty($filters['kind'])) $q->where('kind', $filters['kind']);
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'revoked') $q->where(fn (Builder $q) => $q->where('status', 'revoked')->orWhereNotNull('revoked_at'));
            else $q->where('status', 'issued')->whereNull('revoked_at');
        }
        if (!empty($filters['presence'])) {
            if ($filters['presence'] === 'not_arrived') {
                $q->where(fn (Builder $q) => $q->where('gate_state', 'not_arrived')
                    ->orWhere(fn (Builder $nested) => $nested->whereNull('gate_state')->whereNull('checked_in_at')));
            } elseif ($filters['presence'] === 'inside') {
                $q->where(fn (Builder $q) => $q->where('gate_state', 'inside')
                    ->orWhere(fn (Builder $nested) => $nested->whereNull('gate_state')->whereNotNull('checked_in_at')));
            } else $q->where('gate_state', 'outside');
        }
        if (!empty($filters['q'])) {
            $term = str_replace(['%', '_'], ['\%', '\_'], $filters['q']);
            $q->where(fn (Builder $q) => $q->where('number', 'like', '%'.$term.'%')
                ->orWhereHas('guest', fn (Builder $g) => $g->where('full_name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('phone', 'like', '%'.$term.'%')));
        }
        return $q->orderByDesc('id');
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $tickets = $this->guestQuery($filters)->paginate(25)->withQueryString();
        return view('admin.guest-reports.index', ['tickets' => $tickets, 'events' => $this->events(), 'filters' => $filters]);
    }

    private function overview(?int $eventId): array
    {
        $base = $this->validTickets($eventId);
        $total = (clone $base)->count();
        $inside = (clone $base)->where(fn (Builder $q) => $q->where('gate_state', 'inside')
            ->orWhere(fn (Builder $nested) => $nested->whereNull('gate_state')->whereNotNull('checked_in_at')))->count();
        $outside = (clone $base)->where('gate_state', 'outside')->count();
        $invitations = (clone $base)->where('kind', 'invitation')->count();
        $paidTickets = (clone $base)->where('kind', 'paid')->count();
        $orders = TicketOrder::query()->when($eventId, fn ($q) => $q->where('event_id', $eventId));
        // paid_unfulfilled is captured money needing reconciliation, NOT completed fulfilment.
        $paidRevenue = (clone $orders)->where('status', 'paid')->sum('amount_kobo');
        $unfulfilled = (clone $orders)->where('status', 'paid_unfulfilled');
        return compact('total', 'inside', 'outside', 'invitations', 'paidTickets') + [
            'awaiting' => max(0, $total - $inside - $outside),
            'revenue_kobo' => (int)$paidRevenue,
            'paid_orders' => (clone $orders)->where('status', 'paid')->count(),
            'unfulfilled_orders' => (clone $unfulfilled)->count(),
            'unfulfilled_kobo' => (int)(clone $unfulfilled)->sum('amount_kobo'),
        ];
    }

    public function reports(Request $request)
    {
        $filters = $request->validate(['event_id' => ['nullable', 'integer', 'exists:events,id']]);
        $eventId = !empty($filters['event_id']) ? (int)$filters['event_id'] : null;
        $stats = $this->overview($eventId);
        $categories = $this->validTickets($eventId)->selectRaw('ticket_category_id, kind, COUNT(*) as tickets_count')
            ->with('category:id,name')->groupBy('ticket_category_id','kind')->orderBy('ticket_category_id')->get();
        $recent = TicketCheckinLog::query()->with(['ticket.guest', 'ticket.event', 'actor'])
            ->when($eventId, fn ($q) => $q->whereHas('ticket', fn ($t) => $t->where('event_id', $eventId)))
            ->latest('id')->limit(12)->get();
        return view('admin.guest-reports.reports', compact('stats','categories','recent','filters') + ['events' => $this->events()]);
    }

    public function movements(Request $request)
    {
        $data = $request->validate(['event_id' => ['nullable','integer','exists:events,id']]);
        $id = !empty($data['event_id']) ? (int)$data['event_id'] : null;
        $query = TicketCheckinLog::query()->with(['ticket.guest','ticket.event','actor'])
            ->when($id, fn ($q) => $q->whereHas('ticket', fn ($t) => $t->where('event_id', $id)));
        return view('admin.guest-reports.movements', ['movements' => $query->latest('id')->paginate(30)->withQueryString(), 'events' => $this->events(), 'eventId' => $id]);
    }

    private function safeCsv($value): string
    {
        $value = (string)($value ?? '');
        // Prevent Excel formula injection while leaving ordinary values unchanged.
        if (preg_match('/^[\s\x00-\x1F]*[=+@\-]/u', $value)) $value = "'".$value;
        return $value;
    }

    private function streamCsv(string $name, array $headers, callable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            $rows($out);
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    public function exportGuests(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        return $this->streamCsv('nyfew-guests-'.now()->format('Ymd-His').'.csv',
            ['Event','Ticket','Guest','Email','Phone','Category','Issue Type','Ticket Status','Presence','First Check-in'],
            function ($out) use ($filters) {
                $this->guestQuery($filters)->reorder('id')->chunkById(250, function ($items) use ($out) {
                    foreach ($items as $t) {
                        $presence = $t->gate_state ?: ($t->checked_in_at ? 'inside' : 'not_arrived');
                        fputcsv($out, array_map($this->safeCsv(...), [
                            $t->event?->title,$t->number,$t->guest?->full_name,$t->guest?->email,$t->guest?->phone,
                            $t->category?->name,$t->kind,$t->status,$presence,$t->checked_in_at?->format('Y-m-d H:i:s'),
                        ]));
                    }
                });
            });
    }

    public function exportSummary(Request $request): StreamedResponse
    {
        $data = $request->validate(['event_id' => ['nullable','integer','exists:events,id']]);
        $eventId = !empty($data['event_id']) ? (int)$data['event_id'] : null;
        $stats = $this->overview($eventId);
        $eventName = $eventId ? Event::findOrFail($eventId)->title : 'All Events';
        return $this->streamCsv('nyfew-summary-'.now()->format('Ymd-His').'.csv',
            ['Event','Metric','Value'], function ($out) use ($eventName, $stats) {
                foreach ($stats as $metric => $value) {
                    fputcsv($out, [$this->safeCsv($eventName),$this->safeCsv(str_replace('_',' ',$metric)), $this->safeCsv($value)]);
                }
            });
    }

    public function exportMovements(Request $request): StreamedResponse
    {
        $data = $request->validate(['event_id' => ['nullable','integer','exists:events,id']]);
        $id = !empty($data['event_id']) ? (int)$data['event_id'] : null;
        return $this->streamCsv('nyfew-gate-history-'.now()->format('Ymd-His').'.csv',
            ['Date & Time','Event','Ticket','Guest','Movement','Officer','Reason'],
            function ($out) use ($id) {
                TicketCheckinLog::query()->with(['ticket.guest','ticket.event','actor'])
                    ->when($id, fn ($q) => $q->whereHas('ticket', fn ($t) => $t->where('event_id', $id)))
                    ->chunkById(250, function ($items) use ($out) {
                        foreach ($items as $log) fputcsv($out, array_map($this->safeCsv(...), [
                            $log->created_at?->format('Y-m-d H:i:s'),$log->ticket?->event?->title,
                            $log->ticket?->number,$log->ticket?->guest?->full_name,$log->action,
                            $log->actor?->name,$log->reason,
                        ]));
                    });
            });
    }
}
