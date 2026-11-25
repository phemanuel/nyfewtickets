@extends('layouts.admin')
@section('heading', 'Event Reports')
@push('head')<link rel="stylesheet" href="{{ asset('assets/nyfew/guest-reports.css') }}?v=1">@endpush
@section('content')
<section class="gr-wrap">
    <div class="gr-intro"><div><span class="gr-eyebrow">NYFEW / TICKETING INTELLIGENCE</span><h2>Events at a glance.</h2><p>Live ticket presence, fulfilled sales and transactions requiring attention.</p></div>
    @can('ticket-reports.export')<a class="gr-btn gr-outline" href="{{ route('admin.guest-reports.export-summary', request()->query()) }}">Export summary CSV ↗</a>@endcan</div>
    <form class="gr-filters gr-one-line" method="GET" action="{{ route('admin.guest-reports.reports') }}"><label>Event<select name="event_id"><option value="">All events</option>@foreach($events as $event)<option value="{{ $event->id }}" @selected(($filters['event_id'] ?? '') == $event->id)>{{ $event->title }}</option>@endforeach</select></label><div class="gr-buttons"><button class="gr-btn gr-gold">View report</button><a class="gr-btn gr-outline" href="{{ route('admin.guest-reports.reports') }}">Clear</a></div></form>
    <div class="gr-metrics">
        <div class="gr-metric"><span>Valid tickets issued</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div class="gr-metric"><span>Currently inside</span><strong>{{ number_format($stats['inside']) }}</strong></div>
        <div class="gr-metric"><span>Checked out</span><strong>{{ number_format($stats['outside']) }}</strong></div>
        <div class="gr-metric"><span>Not arrived</span><strong>{{ number_format($stats['awaiting']) }}</strong></div>
        <div class="gr-metric"><span>Paid tickets issued</span><strong>{{ number_format($stats['paidTickets']) }}</strong></div>
        <div class="gr-metric"><span>Invitation tickets</span><strong>{{ number_format($stats['invitations']) }}</strong></div>
        <div class="gr-metric"><span>Fulfilled paid revenue</span><strong>₦{{ number_format($stats['revenue_kobo'] / 100, 2) }}</strong><small>{{ number_format($stats['paid_orders']) }} paid orders</small></div>
        <div class="gr-metric gr-attention"><span>Paid but unfulfilled</span><strong>₦{{ number_format($stats['unfulfilled_kobo'] / 100, 2) }}</strong><small>{{ number_format($stats['unfulfilled_orders']) }} orders requiring attention</small></div>
    </div>
    <div class="gr-two-col">
        <div class="gr-card"><div class="gr-card-heading"><h3>Issued tickets by category</h3></div><div class="gr-table-scroll"><table class="gr-table"><thead><tr><th>Category</th><th>Type</th><th>Issued</th></tr></thead><tbody>
        @forelse($categories as $item)<tr><td>{{ $item->category?->name ?? 'Removed category' }}</td><td>{{ ucfirst($item->kind) }}</td><td>{{ number_format($item->tickets_count) }}</td></tr>@empty<tr><td colspan="3" class="gr-empty">No tickets yet.</td></tr>@endforelse
        </tbody></table></div></div>
        <div class="gr-card"><div class="gr-card-heading"><h3>Latest gate activity</h3><a class="gr-link" href="{{ route('admin.guest-reports.movements', request()->only('event_id')) }}">All activity ↗</a></div><div class="gr-activity">
        @forelse($recent as $log)<div><strong>{{ ucfirst(str_replace('_',' ',$log->action)) }} · {{ $log->ticket?->guest?->full_name ?? 'Unknown' }}</strong><small>{{ $log->ticket?->number ?? '—' }} · {{ $log->actor?->name ?? 'System' }} · {{ $log->created_at?->format('d M Y, g:i A') }}</small></div>@empty<p class="gr-empty">No gate activity yet.</p>@endforelse
        </div></div>
    </div>
    <p class="gr-note">Revenue excludes pending/failed orders. Money received but not fulfilled is shown separately for manual reconciliation. Ticket counts exclude revoked tickets.</p>
</section>
@endsection
