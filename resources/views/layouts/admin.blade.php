<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#080909"><title>@yield('heading','Dashboard') | NYFEW Administration</title><link rel="icon" type="image/x-icon" href="{{ asset('images/nyfew-favicon.ico') }}">
<link rel="stylesheet"
      href="{{ asset('assets/nyfew/theme.css') }}?v=3">
<link rel="stylesheet" href="{{ asset('assets/nyfew/access-management.css') }}">@stack('head')</head>
<body class="admin-body"><div class="mobile-scrim" data-scrim></div><div class="admin-shell">
<aside class="admin-sidebar" data-sidebar aria-label="Admin navigation"><a class="brand brand-with-logo" href="{{ route('admin.dashboard') }}" aria-label="NYFEW Tickets Administration"><img src="{{ asset('images/nyfew-logo.png') }}" alt="NYFEW emblem" class="brand-logo-img"><span class="brand-word">NYFEW <span>TICKETS</span></span></a><div class="sidebar-group">Workspace</div>
<nav class="admin-nav" aria-label="Main admin navigation"><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'current' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif><span class="nav-icon">▦</span> Overview</a>
@can('events.view')<a href="{{ route('admin.events.index') }}" class="{{ request()->routeIs('admin.events.*') ? 'current' : '' }}" @if(request()->routeIs('admin.events.*')) aria-current="page" @endif><span class="nav-icon">▤</span> Events</a>@endcan 
@can('tickets.view')
<a href="{{ route('admin.invitations.index') }}"
   class="{{ request()->routeIs('admin.invitations.*') ? 'current' : '' }}">
    <span class="nav-icon">▣</span>
    Guest Invitations
</a>
@endcan

@can('checkins.scan')
    <a href="{{ route('admin.checkins.index') }}"
       class="{{ request()->routeIs('admin.checkins.*') ? 'current' : '' }}">

        <span class="nav-icon">▣</span>
        Entrance Check-in
    </a>
@endcan

@can('ticket-sales.view')
    <a href="{{ route('admin.ticket-sales.index') }}"
       class="{{ request()->routeIs('admin.ticket-sales.*') ? 'current' : '' }}">
        <span class="nav-icon">▤</span>
        Ticket Sales
    </a>
@endcan

@can('checkins.scan')
    <a href="{{ route('admin.gate.index') }}"
       class="{{ request()->routeIs('admin.gate.*') ? 'current' : '' }}">
        <span class="nav-icon">▣</span>
        Gate Scanner
    </a>
@endcan
@can('staff.view')<a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'current' : '' }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif><span class="nav-icon">♙</span> Staff Accounts</a>@endcan 
@can('roles.view')<a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'current' : '' }}" @if(request()->routeIs('admin.roles.*')) aria-current="page" @endif><span class="nav-icon">⚿</span> Roles &amp; Permissions</a>@endcan
</nav><div class="sidebar-bottom"><div class="sidebar-group" style="padding:0;margin:0 0 14px">Quick access</div><a href="{{ route('home') }}" target="_blank" rel="noopener">↗ &nbsp; View Public Website</a><form method="POST" action="{{ route('admin.logout') }}" style="margin-top:15px">@csrf<button class="btn btn-quiet btn-sm" type="submit" style="width:100%">Sign out</button></form></div></aside>
<div class="admin-main"><header class="admin-topbar"><div class="admin-topbar-left"><button type="button" class="mobile-menu-button" aria-label="Toggle navigation" data-menu-toggle>☰</button><div><div class="kicker">NYFEW / ADMINISTRATION</div><h1>@yield('heading','Overview')</h1></div></div><div class="admin-user"><div><div class="admin-name">{{ auth()->user()->name }}</div><div class="admin-role">Authorised staff</div></div><div class="avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div></div></header>
<main class="admin-content">@if(session('success'))<div class="admin-alert" role="status">✓ &nbsp; {{ session('success') }}</div>@endif @yield('content')</main></div></div>
<script src="{{ asset('assets/nyfew/theme.js') }}" defer></script>@stack('scripts')</body></html>
