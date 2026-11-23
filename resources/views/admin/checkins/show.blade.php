@extends('layouts.admin')
@section('heading', 'Ticket Verification')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/nyfew/checkins.css') }}">
<a class="checkin-back" href="{{ route('admin.checkins.index') }}">← Back to entrance desk</a>

@if($errors->any())
    <div class="error-panel" role="alert">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="admin-card checkin-detail">
    <span class="eyebrow">TICKET VERIFICATION</span>
    @if($ticket->status !== 'issued' || $ticket->revoked_at)
        <div class="checkin-result invalid"><strong>NOT VALID FOR ENTRY</strong><span>This ticket has been revoked or invalidated.</span></div>
    @elseif($ticket->checked_in_at)
        <div class="checkin-result used"><strong>ALREADY CHECKED IN</strong><span>Admitted {{ $ticket->checked_in_at->format('d M Y, H:i:s') }}.</span></div>
    @else
        <div class="checkin-result valid"><strong>VALID · READY FOR ADMISSION</strong><span>Confirm the guest's identity before admitting them.</span></div>
    @endif

    <div class="checkin-identity">
        <div class="checkin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($ticket->guest->full_name, 0, 1)) }}</div>
        <div>
            <h2>{{ $ticket->guest->full_name }}</h2>
            <p class="admin-lead">{{ $ticket->category->name }} · {{ $ticket->event->title }}</p>
            <p><strong>Ticket:</strong> {{ $ticket->number }}</p>
            @if($ticket->guest->organisation)<p><strong>Organisation:</strong> {{ $ticket->guest->organisation }}</p>@endif
        </div>
    </div>

    @if($ticket->status === 'issued' && !$ticket->revoked_at && !$ticket->checked_in_at)
        <form method="POST" action="{{ route('admin.checkins.admit', $ticket) }}" onsubmit="return confirm('Confirm admission for this guest?')">
            @csrf
            <button class="btn btn-gold" type="submit">Confirm check-in</button>
        </form>
    @endif

    @can('checkins.override')
        @if($ticket->checked_in_at)
            <div class="checkin-override">
                <h3>Supervisor: reverse admission</h3>
                <p class="admin-lead">Only reverse an accidental check-in. Every reversal is recorded.</p>
                <form method="POST" action="{{ route('admin.checkins.reverse', $ticket) }}" onsubmit="return confirm('Reverse this admission?')">
                    @csrf
                    <div class="form-group"><label for="reason">Reason (required, at least 10 characters)</label><textarea id="reason" name="reason" rows="3" required minlength="10" maxlength="1000">{{ old('reason') }}</textarea></div>
                    <button type="submit" class="btn btn-outline">Reverse check-in</button>
                </form>
            </div>
        @endif
    @endcan
</div>
<div class="admin-card">
    <h3>Admission audit trail</h3>
    <div class="table-wrap"><table>
        <thead><tr><th>Action</th><th>Officer</th><th>Time</th><th>Reason</th></tr></thead>
        <tbody>
        @forelse($history as $entry)
            <tr><td>{{ ucfirst($entry->action) }}</td><td>{{ $entry->actor?->name ?? 'Former staff' }}</td><td>{{ $entry->created_at?->format('d M Y, H:i:s') }}</td><td>{{ $entry->reason ?? '—' }}</td></tr>
        @empty
            <tr><td colspan="4">No admission activity recorded.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
