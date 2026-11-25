@extends('layouts.admin')
@section('heading', 'Gate Activity')
@push('head')<link rel="stylesheet" href="{{ asset('assets/nyfew/guest-reports.css') }}?v=1">@endpush
@section('content')
<section class="gr-wrap"><div class="gr-intro"><div><span class="gr-eyebrow">NYFEW / AUDIT TRAIL</span><h2>Gate movement history.</h2><p>Every entry, exit, re-entry and supervisor correction remains traceable.</p></div>@can('ticket-reports.export')<a class="gr-btn gr-outline" href="{{ route('admin.guest-reports.export-movements', request()->query()) }}">Export CSV ↗</a>@endcan</div>
<form class="gr-filters gr-one-line" method="GET"><label>Event<select name="event_id"><option value="">All events</option>@foreach($events as $event)<option value="{{ $event->id }}" @selected($eventId == $event->id)>{{ $event->title }}</option>@endforeach</select></label><div class="gr-buttons"><button class="gr-btn gr-gold">Filter</button></div></form>
<div class="gr-card"><div class="gr-card-heading"><h3>Movement log</h3><span>{{ number_format($movements->total()) }} records</span></div><div class="gr-table-scroll"><table class="gr-table"><thead><tr><th>Recorded at</th><th>Event / Ticket</th><th>Guest</th><th>Action</th><th>Officer</th><th>Reason</th></tr></thead><tbody>@forelse($movements as $log)
<tr><td>{{ $log->created_at?->format('d M Y, g:i:s A') }}</td><td>{{ $log->ticket?->event?->title ?? '—' }}<small>{{ $log->ticket?->number ?? '—' }}</small></td><td>{{ $log->ticket?->guest?->full_name ?? '—' }}</td><td><span class="gr-pill">{{ ucfirst(str_replace('_',' ',$log->action)) }}</span></td><td>{{ $log->actor?->name ?? 'System' }}</td><td>{{ $log->reason ?: '—' }}</td></tr>
@empty<tr><td colspan="6" class="gr-empty">No movements found.</td></tr>@endforelse</tbody></table></div><div class="gr-pagination">{{ $movements->links() }}</div></div></section>
@endsection
