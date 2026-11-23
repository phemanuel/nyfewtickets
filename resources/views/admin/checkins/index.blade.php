@extends('layouts.admin')
@section('heading', 'Entrance Check-in')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/nyfew/checkins.css') }}">
<div class="admin-toolbar">
    <div>
        <span class="eyebrow">NYFEW / GATE OPERATIONS</span>
        <h2>Entrance management</h2>
        <p class="admin-lead">Look up an issued ticket, verify the guest, then confirm admission.</p>
    </div>
</div>

@if($errors->any())
    <div class="error-panel" role="alert">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="checkin-metrics">
    <div class="admin-card"><span class="eyebrow">ACTIVE TICKETS</span><h2>{{ number_format($issued) }}</h2></div>
    <div class="admin-card"><span class="eyebrow">CHECKED IN</span><h2>{{ number_format($checkedIn) }}</h2></div>
    <div class="admin-card"><span class="eyebrow">AWAITING ARRIVAL</span><h2>{{ number_format(max(0, $issued - $checkedIn)) }}</h2></div>
</div>

<div class="admin-card checkin-search">
    <h3>Find a guest's ticket</h3>
    <p class="admin-lead">Enter the ticket number, or paste the QR verification URL. This works locally without a phone camera.</p>
    <form method="POST" action="{{ route('admin.checkins.lookup') }}">
        @csrf
        <div class="form-group">
            <label for="event_id">Event (optional)</label>
            <select id="event_id" name="event_id">
                <option value="">All events</option>
                @foreach($events as $event)
                    <option value="{{ $event->id }}" @selected((string) old('event_id', $eventId) === (string) $event->id)>{{ $event->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="reference">Ticket number or QR verification link</label>
            <input id="reference" name="reference" value="{{ old('reference') }}" placeholder="NYF-001-..." required autocomplete="off" autofocus>
        </div>
        <button class="btn btn-gold" type="submit">Verify ticket →</button>
    </form>
</div>

<div class="admin-card">
    <h3>Recent entrance activity</h3>
    <div class="table-wrap"><table>
        <thead><tr><th>Guest</th><th>Ticket</th><th>Event</th><th>Action</th><th>Officer</th><th>Time</th></tr></thead>
        <tbody>
        @forelse($history as $entry)
            <tr>
                <td>{{ $entry->ticket?->guest?->full_name ?? 'Unavailable' }}</td>
                <td><a href="{{ route('admin.checkins.show', $entry->ticket_id) }}">{{ $entry->ticket?->number }}</a></td>
                <td>{{ $entry->ticket?->event?->title }}</td>
                <td>{{ ucfirst($entry->action) }}</td>
                <td>{{ $entry->actor?->name ?? 'Former staff' }}</td>
                <td>{{ $entry->created_at?->format('d M Y, H:i:s') }}</td>
            </tr>
        @empty
            <tr><td colspan="6">No check-in activity yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $history->links() }}
</div>
@endsection
