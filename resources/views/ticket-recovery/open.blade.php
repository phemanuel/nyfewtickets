@extends('layouts.site')
@section('title', 'Secure Ticket Access | NYFEW')
@section('content')
<main style="max-width:620px;margin:8vh auto;padding:24px;color:#f9f4e8"><div style="background:#171815;border:1px solid #6f5c3b;border-radius:18px;padding:32px">
<div style="color:#d7b768;letter-spacing:2px">NYFEW · SECURE TICKETS</div>
<h1>Open your tickets</h1><p>This secure link lets you access your purchased tickets on this or another device for 24 hours from when the email was sent. Keep the link private.</p>
<form action="{{ route('ticket-recovery.consume', ['token'=>$token]) }}" method="POST">@csrf
<button class="btn btn-gold" type="submit">Continue to my tickets →</button></form>
</div></main>
@endsection
