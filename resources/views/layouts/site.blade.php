<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#080909"><title>@yield('title','NYFEW Tickets | Official Events')</title><link rel="icon" type="image/x-icon" href="{{ asset('images/nyfew-favicon.ico') }}"><link rel="stylesheet" href="{{ asset('assets/nyfew/theme.css') }}">@stack('head')</head>
<body>
<header class="site-header"><nav class="shell topnav" aria-label="Public navigation"><a class="brand brand-with-logo" href="{{ route('home') }}" aria-label="NYFEW Tickets home"><img src="{{ asset('images/nyfew-logo.png') }}" alt="NYFEW emblem" class="brand-logo-img"><span class="brand-word">NYFEW <span>TICKETS</span></span></a><div class="site-links"><a href="{{ route('home') }}">Home</a><a href="{{ route('events.index') }}">Events</a></div><div class="site-actions">
    <!-- <a class="btn btn-outline btn-sm" href="{{ route('admin.login') }}">Staff Portal <span aria-hidden="true">↗</span></a> -->
</div></nav></header>
@yield('content')
<footer class="site-footer"><div class="shell"><a class="brand brand-with-logo" href="{{ route('home') }}" aria-label="NYFEW Tickets home"><img src="{{ asset('images/nyfew-logo.png') }}" alt="NYFEW emblem" class="brand-logo-img"><span class="brand-word">NYFEW <span>TICKETS</span></span></a><span>© {{ date('Y') }} NYFEW. Official event access platform.</span><span>Built for extraordinary experiences.</span></div></footer>
<script src="{{ asset('assets/nyfew/theme.js') }}" defer></script>@stack('scripts')</body></html>
