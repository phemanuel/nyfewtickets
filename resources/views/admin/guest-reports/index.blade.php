@extends('layouts.admin')
@section('heading', 'Guest Directory')
@push('head')
<link rel="stylesheet" href="{{ asset('assets/nyfew/guest-reports.css') }}?v=1">
@endpush
@section('content')
<section class="gr-wrap">
    <div class="gr-intro"><div><span class="gr-eyebrow">NYFEW / GUEST MANAGEMENT</span><h2>Every ticket. One directory.</h2><p>Find guests across paid orders and invitations. Admission status updates as gate officers scan.</p></div>
    @can('ticket-reports.export')<a class="gr-btn gr-outline" href="{{ route('admin.guest-reports.export-guests', request()->query()) }}">Export filtered CSV ↗</a>@endcan</div>
    <form method="GET" action="{{ route('admin.guest-reports.index') }}" class="gr-filters">
        <label>Search guests or ticket<input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, email, phone or ticket number"></label>
        <label>Event<select name="event_id"><option value="">All events</option>@foreach($events as $event)<option value="{{ $event->id }}" @selected(($filters['event_id'] ?? '') == $event->id)>{{ $event->title }}</option>@endforeach</select></label>
        <label>Issue type<select name="kind"><option value="">All</option><option value="paid" @selected(($filters['kind'] ?? '') === 'paid')>Paid</option><option value="invitation" @selected(($filters['kind'] ?? '') === 'invitation')>Invitation</option></select></label>
        <label>Presence<select name="presence"><option value="">All</option><option value="not_arrived" @selected(($filters['presence'] ?? '') === 'not_arrived')>Not arrived</option><option value="inside" @selected(($filters['presence'] ?? '') === 'inside')>Inside</option><option value="outside" @selected(($filters['presence'] ?? '') === 'outside')>Outside</option></select></label>
        <label>Validity<select name="status"><option value="">All</option><option value="issued" @selected(($filters['status'] ?? '') === 'issued')>Valid</option><option value="revoked" @selected(($filters['status'] ?? '') === 'revoked')>Revoked</option></select></label>
        <div class="gr-buttons"><button class="gr-btn gr-gold">Apply filters</button><a class="gr-btn gr-outline" href="{{ route('admin.guest-reports.index') }}">Reset</a></div>
    </form>
    <div class="gr-card"><div class="gr-card-heading"><h3>Registered ticket holders</h3><span>{{ number_format($tickets->total()) }} matching tickets</span></div>
        <div class="gr-table-scroll"><table class="gr-table"><thead><tr><th>Guest</th><th>Event</th><th>Ticket / Category</th><th>Type</th><th>Entry status</th><th>Action</th></tr></thead><tbody>
        @forelse($tickets as $ticket)
        @php $presence = $ticket->gate_state ?: ($ticket->checked_in_at ? 'inside' : 'not_arrived'); $invalid = $ticket->status !== 'issued' || $ticket->revoked_at !== null; @endphp
        <tr><td><strong>{{ $ticket->guest?->full_name ?? 'Unknown guest' }}</strong><small>{{ $ticket->guest?->email ?: ($ticket->guest?->phone ?: 'No contact') }}</small></td>
        <td>{{ $ticket->event?->title ?? '—' }}</td>
        <td><strong>{{ $ticket->number }}</strong><small>{{ $ticket->category?->name ?? '—' }}</small></td>
        <td>{{ ucfirst($ticket->kind) }}</td>
        <td>@if($invalid)<span class="gr-pill gr-bad">Invalid / revoked</span>@elseif($presence === 'inside')<span class="gr-pill gr-good">Inside</span>@elseif($presence === 'outside')<span class="gr-pill gr-warn">Outside</span>@else<span class="gr-pill">Not arrived</span>@endif</td>
        <td>@can('checkins.scan')<a class="gr-link" href="{{ route('admin.gate.show', ['ticket' => $ticket->id]) }}">Gate record ↗</a>@else<span>—</span>@endcan</td></tr>
        @empty<tr><td colspan="6" class="gr-empty">No tickets match your filters.</td></tr>@endforelse
        </tbody></table></div>
        <div class="gr-pagination">{{ $tickets->links() }}</div>
    </div>
</section>
@endsection
