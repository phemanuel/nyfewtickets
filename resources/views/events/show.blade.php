@extends('layouts.site')
@section('title', $event->title . ' | NYFEW Tickets')
@push('head')
<link rel="stylesheet" href="{{ asset('assets/nyfew/events-premium.css') }}?v=1">
@endpush
@section('content')
@php
    $paidCategories = $event->ticketCategories()->where('type', 'paid')->where('is_active', true)->get();
    $salesEnabled = (bool) config('nyfew_paystack.enabled');
@endphp
<div class="nx-events nx-detail-page">
    <main class="shell">
        <nav class="nx-crumb nx-detail-crumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('events.index') }}">Events</a><span>/</span><span>{{ $event->title }}</span></nav>
        <div class="nx-detail-grid">
            <section class="nx-detail-main">
                <div class="nx-detail-poster">
                    @if($event->poster_url)
                        <img src="{{ $event->poster_url }}" alt="{{ $event->title }} artwork">
                    @else
                        <div class="nx-detail-placeholder"><span>NYFEW PRESENTS</span><strong>AN EXPERIENCE<br>TO REMEMBER.</strong><small>OFFICIAL NYFEW EVENT</small></div>
                    @endif
                </div>
                <div class="nx-about"><span class="nx-kicker">ABOUT THE EXPERIENCE</span><h2>Everything you need to know.</h2><p>{{ $event->description ?: 'More information about this event will be announced soon.' }}</p></div>
            </section>
            <aside class="nx-booking" aria-label="Event information and admission">
                <div class="nx-booking-inner">
                    <span class="nx-event-chip">✦ &nbsp; NYFEW OFFICIAL EVENT</span>
                    <h1>{{ $event->title }}</h1>
                    <p class="nx-booking-intro">Your experience starts here. Explore the event details and secure your place.</p>
                    <div class="nx-detail-facts">
                        <div class="nx-fact"><span class="nx-fact-icon" aria-hidden="true">◷</span><div><small>DATE &amp; TIME</small><strong>{{ $event->starts_at?->format('l, d F Y') ?? 'To be announced' }}</strong><span>{{ $event->starts_at?->format('g:i A') ?? 'Time to be announced' }}</span></div></div>
                        <div class="nx-fact"><span class="nx-fact-icon" aria-hidden="true">⌖</span><div><small>VENUE</small><strong>{{ $event->venue ?: 'Venue to be announced' }}</strong></div></div>
                    </div>
                    <div class="nx-booking-purchase">
                        <span class="nx-kicker">YOUR ADMISSION</span>
                        @if(!$salesEnabled)
                            <div class="nx-notice"><strong>Online ticket sales are currently closed.</strong><span>Please check back for availability.</span></div>
                        @elseif($paidCategories->isEmpty())
                            <div class="nx-notice"><strong>Paid tickets are not available yet.</strong><span>Please check back once this event's paid categories are released.</span></div>
                        @else
                            <p class="nx-ready">Choose your ticket category and complete your booking securely.</p>
                            <a href="{{ route('sales.show', ['event' => $event->slug]) }}" class="nx-btn nx-btn-gold nx-buy-button">Buy Tickets <span aria-hidden="true">↗</span></a>
                            <div class="nx-secure-note">Secure checkout powered by Paystack</div>
                        @endif
                    </div>
                    <a class="nx-back-link" href="{{ route('events.index') }}">← Browse all events</a>
                </div>
            </aside>
        </div>
    </main>
    <section class="nx-retrieve-band"><div class="shell nx-retrieve-inner"><div><span class="nx-kicker">EXISTING BOOKING</span><h2>Need your ticket again?</h2><p>Retrieve your order and access your existing QR tickets securely.</p></div><a class="nx-btn nx-btn-outline" href="{{ url('/tickets/retrieve') }}">Retrieve my tickets ↗</a></div></section>
</div>
@endsection
