@extends('layouts.admin')
@section('heading', 'Gate Scanner')
@push('head')
<link rel="stylesheet" href="{{ asset('assets/nyfew/gate-scanner.css') }}">
@endpush
@section('content')
<div class="gate-wrap">
    <div class="gate-topline">
        <div><span class="gate-kicker">NYFEW · EVENT ENTRANCE</span><h2>Scan &amp; verify</h2><p>Signed in as {{ auth()->user()->name }}. Select the event before scanning.</p></div>
        <span class="gate-live-pill">SECURE GATE DESK</span>
    </div>
    @if($errors->any())
        <div class="gate-alert gate-alert-error" role="alert">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif
    <div class="gate-metrics">
        <div><small>ISSUED · VALID</small><strong>{{ number_format($total) }}</strong></div>
        <div><small>CURRENTLY INSIDE</small><strong>{{ number_format($inside) }}</strong></div>
        <div><small>CHECKED OUT</small><strong>{{ number_format($outside) }}</strong></div>
        <div><small>NOT ARRIVED</small><strong>{{ number_format(max(0, $total - $inside - $outside)) }}</strong></div>
    </div>
    <div class="gate-columns">
        <section class="gate-panel">
            <h3>Camera scanner</h3>
            <p>Use the rear camera for fastest scanning. Scanning only verifies the ticket; the officer must confirm admission or exit.</p>
            <div id="nyfew-camera" class="gate-camera" aria-label="QR camera scanner"><div id="gate-reader" style="width:100%;max-width:560px"></div><div id="gate-camera-idle" class="gate-camera-idle">Camera not started</div></div>
            <div class="gate-controls">
                <button type="button" id="gate-start" class="gate-gold-btn">Start camera</button>
                <button type="button" id="gate-stop" class="gate-muted-btn" disabled>Stop camera</button>
            </div>
            <div id="gate-camera-message" class="gate-hint" role="status" aria-live="polite">Camera scanning requires HTTPS on a phone, or localhost on a desktop.</div>
        </section>
        <section class="gate-panel">
            <h3>Verify ticket</h3>
            <p>Paste the QR URL or enter a ticket number if the guest's phone cannot be scanned.</p>
            <form id="gate-lookup-form" method="POST" action="{{ route('admin.gate.lookup') }}">
                @csrf
                <label for="gate-event">Event</label>
                <select id="gate-event" name="event_id" required>
                    <option value="">Select the event</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected((string) old('event_id', $selectedEvent) === (string) $event->id)>{{ $event->title }}</option>
                    @endforeach
                </select>
                <label for="gate-reference">QR link or ticket number</label>
                <input id="gate-reference" type="text" name="reference" autocomplete="off" required placeholder="NYF-001-..." value="{{ old('reference') }}">
                <button type="submit" class="gate-gold-btn gate-full">Verify guest</button>
            </form>
            <div class="gate-security-note"><strong>Identity checkpoint</strong><p>Re-entry requires the correct guest, a physical wristband/hand stamp where issued, and an OUTSIDE status. Never admit someone simply because they possess a screenshot.</p></div>
        </section>
    </div>
    <section class="gate-panel gate-history">
        <h3>Recent gate activity</h3>
        <div class="gate-table-wrap"><table><thead><tr><th>Guest</th><th>Event</th><th>Movement</th><th>Officer</th><th>Time</th></tr></thead><tbody>
            @forelse($history as $log)
                <tr><td>{{ $log->ticket?->guest?->full_name ?? 'Unknown guest' }}</td><td>{{ $log->ticket?->event?->title ?? '—' }}</td><td>{{ ucfirst(str_replace('_',' ', $log->action)) }}</td><td>{{ $log->actor?->name ?? 'Former staff' }}</td><td>{{ $log->created_at?->format('d M Y H:i:s') }}</td></tr>
            @empty
                <tr><td colspan="5">No gate activity yet.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
</div>
@endsection
@push('scripts')
<script src="{{ asset('assets/nyfew/vendor/html5-qrcode.min.js') }}" defer></script>
<script src="{{ asset('assets/nyfew/gate-scanner.js') }}?v=2" defer></script>
@endpush
