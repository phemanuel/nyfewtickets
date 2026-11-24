@extends('layouts.site')
@section('title',$event->title.' | NYFEW Tickets')
@section('content')<main class="shell"><article class="event-detail"><div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / <a href="{{ route('events.index') }}">Events</a> / {{ $event->title }}</div><span class="eyebrow">NYFEW Official Event</span><h1>{{ $event->title }}</h1><div class="event-meta"><span>◷ &nbsp; {{ $event->starts_at->format('l, d F Y · g:i A') }}</span><span>⌖ &nbsp; {{ $event->venue ?: 'Venue to be announced' }}</span></div><div class="panel"><h2 style="font-size:1.35rem">About this experience</h2><div style="color:var(--soft);white-space:pre-line">{{ $event->description ?: 'More information about this event will be announced soon.' }}</div>@if(!config('nyfew_paystack.enabled'))
    <div class="info-strip">
        <strong>Online ticket sales are currently closed.</strong>
        <div style="margin-top:5px;font-size:.87rem">Please check back for ticket availability.</div>
    </div>
@elseif(!$event->ticketCategories()->where('type', 'paid')->where('is_active', true)->exists())
    <div class="info-strip">
        <strong>Paid tickets are not available for this event yet.</strong>
        <div style="margin-top:5px;font-size:.87rem">Please check back once tickets are released.</div>
    </div>
@else
    <div style="margin:22px 0">
        <a href="{{ route('sales.show', ['event' => $event->slug]) }}"
           class="btn btn-gold">Buy Tickets &rarr;</a>
    </div>
@endif<a class="btn btn-outline" href="{{ route('events.index') }}">← Browse other events</a></div></article></main>@endsection
