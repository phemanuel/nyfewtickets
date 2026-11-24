@extends('layouts.site')
@section('title', 'Ticket Link Expired | NYFEW Tickets')
@section('content')
<main style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:55px 20px;background:#0b0b0a">
    <section style="max-width:560px;width:100%;text-align:center;background:#171816;border:1px solid #625137;border-radius:18px;padding:44px 28px;color:#f9f4e8">
        <div aria-hidden="true" style="font-size:44px;color:#d7b768;margin-bottom:12px">&#9203;</div>
        <div style="font-size:12px;letter-spacing:2.5px;color:#d7b768;margin-bottom:16px">NYFEW TICKETS · SECURE ACCESS</div>
        <h1 style="font-size:30px;line-height:1.2;margin-bottom:18px">This access link is no longer valid</h1>
        <p style="color:#c6bdaa;line-height:1.75;margin-bottom:26px">Your link has expired or has already been used under our previous access policy. Your purchased tickets remain available; request a fresh link to access them.</p>
        <a href="{{ route('ticket-recovery.index') }}" style="display:inline-block;background:#d7b768;color:#17130b;padding:14px 22px;border-radius:9px;font-weight:bold;text-decoration:none">Request a new access link</a>
        <p style="font-size:13px;color:#b5aa95;line-height:1.6;margin-top:22px">Use the purchase email and order reference. New links are valid for 24 hours.</p>
    </section>
</main>
@endsection
