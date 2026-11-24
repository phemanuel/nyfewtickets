@extends('layouts.site')
@section('title', 'Ticket Order · NYFEW Tickets')
@push('head')
<style>.nyf-receipt{max-width:900px;margin:40px auto 80px;padding:0 20px;color:#f9f4e8}.nyf-receipt .panel{background:#181a17;border:1px solid #51482f;border-radius:18px;padding:22px;margin:20px 0}.nyf-receipt .gold{color:#ecd28f}.nyf-receipt .sub{color:#bdb4a0}.nyf-receipt .ticket{border-top:1px solid #504935;padding:18px 0}.nyf-receipt a{color:#edd38b;text-decoration:underline}</style>
@endpush
@section('content')
<main class="nyf-receipt"><div class="gold">NYFEW · PAYMENT STATUS</div><h1>{{ $order->event->title }}</h1>
<div class="panel"><strong>Order {{ $order->reference }}</strong><p class="sub">Total: ₦{{ number_format($order->amount_kobo / 100,2) }}</p>
@if($order->status==='paid')
<h2 style="color:#a9e1b0">Payment confirmed. Your tickets are ready.</h2>
<p class="sub">Save or print your tickets from this page. A secure access link is also sent to the buyer email address. Do not share your QR codes publicly.</p>
@foreach($order->attendees as $attendee)
    @if($attendee->ticket && $attendee->ticket->qr_token_encrypted)
        <div class="ticket"><h3>{{ $attendee->full_name }}</h3><p class="sub">{{ $attendee->category->name }} · Ticket {{ $attendee->ticket->number }}</p>
        <a href="{{ route('sales.ticket-download', ['reference'=>$order->reference,'ticket'=>$attendee->ticket->id]) }}">Download PDF ticket</a></div>
    @endif
@endforeach
@elseif($order->status==='paid_unfulfilled')
<h2>Payment received — manual assistance required</h2><p>We received your payment, but the reserved tickets were no longer available. Do not pay again. Contact NYFEW support with order reference {{ $order->reference }} to resolve or refund your order.</p>
@else
<h2>Awaiting payment confirmation</h2><p class="sub">Your tickets have not been issued. If payment was completed, refresh this page shortly. Do not make another payment until the status is confirmed.</p>
@endif
</div><p><a href="{{ route('ticket-recovery.index') }}">Need your tickets later? Retrieve with your order reference and email.</a></p></main>
@endsection
