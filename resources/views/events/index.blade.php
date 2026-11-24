@extends('layouts.site')
@section('title', 'Explore Events | NYFEW Tickets')
@push('head')
<link rel="stylesheet" href="{{ asset('assets/nyfew/events-premium.css') }}?v=1">
@endpush
@section('content')
<div class="nx-events">
    <section class="nx-directory-hero">
        <div class="shell nx-directory-hero-inner">
            <nav class="nx-crumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>Events</span></nav>
            <span class="nx-kicker">THE OFFICIAL NYFEW EVENT CALENDAR</span>
            <h1>Find your next <em>unforgettable moment.</em></h1>
            <p>Discover the events, performances and experiences worth showing up for. Your next great memory starts here.</p>
            <div class="nx-hero-bottom"><span>OFFICIAL EVENTS</span><span class="nx-line"></span><span>SECURE ADMISSION</span><span class="nx-line"></span><span>NYFEW EXPERIENCES</span></div>
        </div>
    </section>
    <section class="nx-directory-content shell" aria-labelledby="nx-all-events-heading">
        <div class="nx-heading-row"><div><span class="nx-kicker">WHAT'S ON</span><h2 id="nx-all-events-heading">Explore <em>the lineup.</em></h2></div><span class="nx-count">{{ $events->total() }} {{ \Illuminate\Support\Str::plural('event', $events->total()) }}</span></div>
        <div class="nx-card-grid">
            @forelse($events as $event)
                <article class="nx-card">
                    <a href="{{ route('events.show', $event) }}" class="nx-card-media" aria-label="View {{ $event->title }}">
                        @if($event->poster_url)
                            <img src="{{ $event->poster_url }}" alt="{{ $event->title }} artwork" loading="lazy">
                        @else
                            <div class="nx-card-placeholder"><span>NYFEW</span><strong>THE EXPERIENCE</strong><small>OFFICIAL EVENT</small></div>
                        @endif
                        <span class="nx-card-label">OFFICIAL EVENT</span>
                    </a>
                    <div class="nx-card-content">
                        <div class="nx-card-date">{{ $event->starts_at?->format('d M Y') ?? 'DATE TO BE ANNOUNCED' }}</div>
                        <h3><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></h3>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) ($event->description ?: 'Discover event details and admission information.')), 125) }}</p>
                        <div class="nx-card-location"><span aria-hidden="true">⌖</span> {{ $event->venue ?: 'Venue to be announced' }}</div>
                        <a class="nx-card-action" href="{{ route('events.show', $event) }}">View event &amp; tickets <span aria-hidden="true">↗</span></a>
                    </div>
                </article>
            @empty
                <div class="nx-empty"><span class="nx-kicker">COMING SOON</span><h3>Something extraordinary is on the way.</h3><p>There are no public events listed at the moment. Check back for the next NYFEW announcement.</p><a class="nx-btn nx-btn-outline" href="{{ route('home') }}">Return home →</a></div>
            @endforelse
        </div>
        @if($events->hasPages())<div class="nx-pagination">{{ $events->links() }}</div>@endif
    </section>
    <section class="nx-retrieve-band"><div class="shell nx-retrieve-inner"><div><span class="nx-kicker">ALREADY BOOKED?</span><h2>Your tickets are always within reach.</h2><p>Retrieve your purchased tickets securely using your order reference and email address.</p></div><a class="nx-btn nx-btn-gold" href="{{ url('/tickets/retrieve') }}">Retrieve my tickets ↗</a></div></section>
</div>
@endsection
