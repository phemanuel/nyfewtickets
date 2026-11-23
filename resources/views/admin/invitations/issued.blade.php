@extends('layouts.admin')
@section('heading','Invitation Issued')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/nyfew/invitations.css') }}">
<div class="admin-toolbar"><div><span class="eyebrow">ADMISSION ISSUED</span><h2>Ticket created successfully</h2><p class="admin-lead">Save the PDF now. For security, the QR token cannot be recovered later from the database.</p></div><a href="{{ route('admin.invitations.index',['event_id'=>$ticket->event_id]) }}" class="btn btn-outline btn-sm">← All invitations</a></div>
<div class="admin-card inv-issued"><div class="inv-ticket-art">✦</div><h3>{{ $ticket->guest->full_name }}</h3><p>{{ $ticket->event->title }} · {{ $ticket->category->name }}</p><strong>{{ $ticket->number }}</strong><div style="margin-top:25px"><a class="btn btn-gold" href="{{ route('admin.invitations.download',['ticket'=>$ticket,'token'=>$token]) }}">↓ Download PDF ticket</a></div><p class="form-help">Keep this page private. The download link contains the one-time-revealed QR token.</p></div>
@endsection
