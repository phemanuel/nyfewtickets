@extends('layouts.admin')
@section('heading', 'Gate Verification')
@push('head')<link rel="stylesheet" href="{{ asset('assets/nyfew/gate-scanner.css') }}">@endpush
@section('content')
<div class="gate-wrap">
    <a class="gate-back" href="{{ route('admin.gate.index', ['event_id' => $ticket->event_id]) }}">← Back to scanner</a>
    @if($errors->any())
        <div class="gate-alert gate-alert-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif
    <div class="gate-panel gate-guest-card">
        <span class="gate-kicker">NYFEW · ADMISSION VERIFICATION</span>
        @if($ticket->status !== 'issued' || $ticket->revoked_at)
            <div class="gate-banner gate-danger"><strong>INVALID TICKET</strong><p>Ticket is revoked or invalid. Do not admit.</p></div>
        @elseif($state === 'inside')
            <div class="gate-banner gate-danger"><strong>ALREADY INSIDE</strong><p>This ticket is currently admitted. Reject another entry. Use Check Out only when the actual guest is leaving.</p></div>
        @elseif($state === 'outside')
            <div class="gate-banner gate-warning"><strong>CHECKED OUT — RE-ENTRY</strong><p>Confirm the guest's identity and wristband before allowing re-entry.</p></div>
        @else
            <div class="gate-banner gate-success"><strong>VALID — FIRST ENTRY</strong><p>Verify the person holding the ticket before admitting them.</p></div>
        @endif
        <div class="gate-identity">
            @if($ticket->guest?->photo_path)
                <img src="{{ route('admin.gate.photo', ['ticket' => $ticket]) }}" alt="Guest photo" class="gate-portrait">
            @else
                <div class="gate-portrait gate-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($ticket->guest?->full_name ?? '?', 0, 1)) }}</div>
            @endif
            <div>
                <h2>{{ $ticket->guest?->full_name ?? 'Guest details unavailable' }}</h2>
                <p>{{ $ticket->category?->name }} · {{ $ticket->event?->title }}</p>
                <p><strong>Ticket:</strong> {{ $ticket->number }}</p>
                @if($ticket->guest?->organisation)<p><strong>Organisation:</strong> {{ $ticket->guest->organisation }}</p>@endif
                <p><strong>First admission:</strong> {{ $ticket->checked_in_at?->format('d M Y H:i:s') ?? 'Not yet admitted' }}</p>
                <p><strong>Last entrance:</strong> {{ $ticket->last_entered_at ? \Illuminate\Support\Carbon::parse($ticket->last_entered_at)->format('d M Y H:i:s') : '—' }}</p>
                <p><strong>Last exit:</strong> {{ $ticket->last_exited_at ? \Illuminate\Support\Carbon::parse($ticket->last_exited_at)->format('d M Y H:i:s') : '—' }}</p>
            </div>
        </div>
        @if($ticket->status === 'issued' && !$ticket->revoked_at)
            <div class="gate-actions">
                @if($state !== 'inside')
                    <form method="POST" action="{{ route('admin.gate.enter', $ticket) }}" onsubmit="return confirm('Have you verified this person\'s identity and wristband (if applicable)?')">
                        @csrf <button class="gate-gold-btn" type="submit">{{ $state === 'outside' ? 'Confirm Re-entry' : 'Confirm First Entry' }}</button>
                    </form>
                @endif
                @if($state === 'inside')
                    @can('checkins.exit')
                        <form method="POST" action="{{ route('admin.gate.exit', $ticket) }}" onsubmit="return confirm('Is this guest physically leaving the venue now?')">
                            @csrf <button class="gate-muted-btn" type="submit">Check Out Guest</button>
                        </form>
                    @endcan
                    @can('checkins.override')
                        <div class="gate-override">
                            <h3>Supervisor: correct missing exit</h3>
                            <p>Use only after independent identity verification; every correction is audited.</p>
                            <form method="POST" action="{{ route('admin.gate.correct-exit', $ticket) }}" onsubmit="return confirm('Record this supervisor correction?')">
                                @csrf
                                <label for="gate-reason">Reason (at least 10 characters)</label>
                                <textarea id="gate-reason" name="reason" required minlength="10" maxlength="1000">{{ old('reason') }}</textarea>
                                <button type="submit" class="gate-muted-btn">Record correction</button>
                            </form>
                        </div>
                    @endcan
                @endif
            </div>
        @endif
    </div>
    <div class="gate-panel gate-history">
        <h3>Complete movement history</h3>
        <div class="gate-table-wrap"><table><thead><tr><th>Movement</th><th>Officer</th><th>Time</th><th>Reason</th></tr></thead><tbody>
            @forelse($history as $item)
                <tr><td>{{ ucfirst(str_replace('_', ' ', $item->action)) }}</td><td>{{ $item->actor?->name ?? 'Former staff' }}</td><td>{{ $item->created_at?->format('d M Y H:i:s') }}</td><td>{{ $item->reason ?: '—' }}</td></tr>
            @empty <tr><td colspan="4">No movements recorded yet.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
</div>
@endsection
