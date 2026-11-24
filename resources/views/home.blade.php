@extends('layouts.site')

@section('title', 'NYFEW Tickets | Official Events & Experiences')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/nyfew/home-premium.css') }}?v=1">
@endpush

@section('content')
@php
    // Uses only the $events collection already passed by the existing home controller.
    $featuredEvent = $events->first();
    $otherEvents = $events->skip(1);
    $featuredHasPoster = $featuredEvent && $featuredEvent->poster_url;
@endphp

<div class="ny-home">
    <section class="ny-hero" aria-labelledby="ny-home-title">
        <div class="ny-hero-glow" aria-hidden="true"></div>
        <div class="shell ny-hero-grid">
            <div class="ny-hero-copy">
                <div class="ny-overline"><span class="ny-overline-line"></span> THE OFFICIAL NYFEW TICKETING PLATFORM</div>
                <div class="ny-hero-status"><span class="ny-live-dot"></span> Discover upcoming experiences</div>
                <h1 id="ny-home-title">The moments<br>you <em>live for.</em></h1>
                <p>From the anticipation to the final applause. Find official NYFEW events, reserve your place and keep your admission tickets in one secure experience.</p>
                <div class="ny-hero-ctas">
                    <a href="{{ route('events.index') }}" class="ny-button ny-button-gold">Explore events <span aria-hidden="true">↗</span></a>
                    <a href="{{ url('/tickets/retrieve') }}" class="ny-button ny-button-subtle">Retrieve my tickets <span aria-hidden="true">→</span></a>
                </div>
                <div class="ny-trust-row" aria-label="Ticketing benefits">
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg> Official event listings</span>
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 8h3v3H8zM14 8h2M14 11h2M8 15h2M12 15h4"/></svg> Unique QR tickets</span>
                </div>
            </div>

            <div class="ny-hero-visual">
                <div class="ny-visual-orbit ny-orbit-one" aria-hidden="true"></div>
                <div class="ny-visual-orbit ny-orbit-two" aria-hidden="true"></div>
                @if($featuredEvent && $featuredHasPoster)
                    <a href="{{ route('events.show', $featuredEvent) }}" class="ny-poster-wrap" aria-label="View {{ $featuredEvent->title }}">
                        <img src="{{ $featuredEvent->poster_url }}" alt="{{ $featuredEvent->title }} event artwork" width="864" height="1080" fetchpriority="high">
                        <span class="ny-poster-shine" aria-hidden="true"></span>
                    </a>
                @else
                    <div class="ny-generic-poster" aria-hidden="true">
                        <div class="ny-generic-small">NYFEW PRESENTS</div>
                        <div class="ny-generic-title">A NIGHT<br><span>TO REMEMBER.</span></div>
                        <div class="ny-generic-rule"></div>
                        <div class="ny-generic-small">OFFICIAL EXPERIENCES</div>
                    </div>
                @endif
                <div class="ny-floating-tag"><span class="ny-floating-star">✦</span><span><strong>THE EXPERIENCE</strong><small>Starts right here</small></span></div>
            </div>
        </div>
        <div class="ny-hero-bottom shell"><span>DISCOVER <span class="ny-dot-sep">·</span> BOOK <span class="ny-dot-sep">·</span> EXPERIENCE</span><a href="#ny-events">SCROLL TO EXPLORE <span aria-hidden="true">↓</span></a></div>
    </section>

    <section class="ny-section ny-feature-section" id="ny-events" aria-labelledby="ny-events-heading">
        <div class="shell">
            <div class="ny-section-head">
                <div><div class="ny-overline"><span class="ny-overline-line"></span> WHAT'S HAPPENING</div><h2 id="ny-events-heading">Events worth <em>showing up for.</em></h2><p>Every great memory starts with a plan. Find yours below.</p></div>
                <a class="ny-text-link" href="{{ route('events.index') }}">Browse all events <span aria-hidden="true">↗</span></a>
            </div>

            @if($featuredEvent)
                <article class="ny-feature-event">
                    <div class="ny-feature-art {{ $featuredHasPoster ? 'ny-feature-art-photo' : 'ny-feature-art-generic' }}">
                        @if($featuredHasPoster)
                            <img src="{{ $featuredEvent->poster_url }}" alt="{{ $featuredEvent->title }} event artwork" loading="lazy" width="864" height="1080">
                        @else
                            <div class="ny-feature-generic-title">NYFEW<br><span>EXPERIENCE</span></div>
                        @endif
                    </div>
                    <div class="ny-feature-body">
                        <span class="ny-event-tag"><span></span> FEATURED EVENT</span>
                        <div class="ny-feature-date">
                            <span class="ny-date-day">{{ $featuredEvent->starts_at?->format('d') ?? '—' }}</span>
                            <span>{{ $featuredEvent->starts_at?->format('M') ?? 'TBA' }}<br>{{ $featuredEvent->starts_at?->format('Y') ?? '' }}</span>
                        </div>
                        <h3>{{ $featuredEvent->title }}</h3>
                        <p class="ny-feature-description">{{ \Illuminate\Support\Str::limit(strip_tags((string) ($featuredEvent->description ?: 'Discover the details, reserve your place and prepare for an unforgettable NYFEW experience.')), 190) }}</p>
                        <div class="ny-event-meta">
                            <span><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> {{ $featuredEvent->starts_at?->format('D, d M Y · g:i A') ?? 'Date to be announced' }}</span>
                            <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12Z"/><circle cx="12" cy="9" r="2"/></svg> {{ $featuredEvent->venue ?: 'Venue to be announced' }}</span>
                        </div>
                        <a href="{{ route('events.show', $featuredEvent) }}" class="ny-button ny-button-gold ny-feature-button">View event & tickets <span aria-hidden="true">↗</span></a>
                    </div>
                </article>
            @else
                <div class="ny-empty-events"><span class="ny-overline">ON THE HORIZON</span><h3>Something extraordinary is coming.</h3><p>Our next NYFEW event will appear here when details are announced.</p><a class="ny-button ny-button-subtle" href="{{ route('events.index') }}">Explore events <span aria-hidden="true">→</span></a></div>
            @endif

            @if($otherEvents->isNotEmpty())
                <div class="ny-more-heading"><h3>More to experience</h3><span>{{ $otherEvents->count() }} {{ \Illuminate\Support\Str::plural('event', $otherEvents->count()) }}</span></div>
                <div class="ny-event-grid">
                    @foreach($otherEvents as $event)
                        <a href="{{ route('events.show', $event) }}" class="ny-small-event">
                            @if($event->poster_url)<img src="{{ $event->poster_url }}" alt="{{ $event->title }} artwork" loading="lazy" style="width:100%;height:175px;object-fit:cover;border-radius:10px;margin-bottom:15px">@endif
                            <div class="ny-small-event-top"><span class="ny-event-tag">NYFEW EVENT</span><span class="ny-small-arrow" aria-hidden="true">↗</span></div>
                            <div class="ny-small-date">{{ $event->starts_at?->format('d M Y') ?? 'Date TBA' }}</div>
                            <h4>{{ $event->title }}</h4>
                            <div class="ny-small-location">{{ $event->venue ?: 'Venue to be announced' }}</div>
                            <span class="ny-small-footer">View details <span aria-hidden="true">→</span></span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="ny-process" id="experience" aria-labelledby="ny-process-title">
        <div class="shell">
            <div class="ny-process-head"><div class="ny-overline"><span class="ny-overline-line"></span> SEAMLESS FROM START TO FINISH</div><h2 id="ny-process-title">Your entry, <em>simplified.</em></h2><p>Less hassle getting in. More time enjoying the moment.</p></div>
            <div class="ny-process-grid">
                <div class="ny-process-step"><span class="ny-step-num">01</span><div class="ny-step-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><rect x="4" y="7" width="24" height="21" rx="3"/><path d="M4 13h24M10 4v6M22 4v6M10 19h5"/></svg></div><h3>Explore events</h3><p>Find official NYFEW experiences and see everything you need before attending.</p></div>
                <div class="ny-process-step"><span class="ny-step-num">02</span><div class="ny-step-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M5 10a4 4 0 0 0 4-4h14a4 4 0 0 0 4 4v5a3 3 0 0 0 0 6v5H9a4 4 0 0 0-4-4v-5a3 3 0 0 0 0-6Z"/><path d="M16 7v18" strokeDasharray="2 3"/></svg></div><h3>Get your ticket</h3><p>Purchase available tickets securely or use an official invitation issued to you.</p></div>
                <div class="ny-process-step"><span class="ny-step-num">03</span><div class="ny-step-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M5 12V5h7M20 5h7v7M27 20v7h-7M12 27H5v-7M11 11h5v5h-5zM21 12h1M21 17h2M12 21h2M18 22h4"/></svg></div><h3>Show up & shine</h3><p>Present your unique QR ticket at the entrance for verification and check-in.</p></div>
            </div>
        </div>
    </section>

    <section class="ny-retrieve-section" aria-labelledby="ny-retrieve-title"><div class="shell ny-retrieve-inner"><div><div class="ny-overline"><span class="ny-overline-line"></span> ALREADY BOOKED?</div><h2 id="ny-retrieve-title">Your tickets. <em>Always within reach.</em></h2><p>Need another copy of your admission pass? Retrieve tickets from a completed order using your purchase email and order reference.</p></div><a href="{{ url('/tickets/retrieve') }}" class="ny-button ny-button-gold">Retrieve tickets <span aria-hidden="true">↗</span></a></div></section>
</div>
@endsection
