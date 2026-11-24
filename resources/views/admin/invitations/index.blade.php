@extends('layouts.admin')
@section('heading','Guest Invitations')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/nyfew/invitations.css') }}">
<div class="admin-toolbar">
 <div><span class="eyebrow">NYFEW / ADMISSION MANAGEMENT</span><h2>Guest invitations</h2><p class="admin-lead">Manage complimentary QR tickets by event.</p></div>
 <div class="inv-actions">@can('tickets.issue')<a class="btn btn-gold" href="{{ route('admin.invitations.create') }}">+ Issue invitation</a>@endcan</div>
</div>
<div class="admin-card">
<form method="GET" action="{{ route('admin.invitations.index') }}" class="inv-filter">
 <div class="form-group"><label for="event_id">Event</label><select id="event_id" name="event_id">@foreach($events as $item)<option value="{{ $item->id }}" @selected($event?->id===$item->id)>{{ $item->title }}</option>@endforeach</select></div>
 <div class="form-group"><label for="search">Find guest or ticket number</label><input id="search" name="search" value="{{ request('search') }}" placeholder="Name, email, phone, ticket number"></div>
 <button type="submit" class="btn btn-outline">Search</button>
</form>
<div class="table-wrap"><table><thead><tr><th>Guest</th><th>Ticket</th><th>Category</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody>
@forelse($tickets as $ticket)
<tr><td><strong>{{ $ticket->guest->full_name }}</strong><div class="table-muted">{{ $ticket->guest->email ?: $ticket->guest->phone ?: 'No contact supplied' }}</div></td><td>{{ $ticket->number }}</td><td>{{ $ticket->category->name }}</td><td><span class="status {{ $ticket->status==='issued' ? 'published' : '' }}">{{ ucfirst($ticket->status) }}</span></td><td>{{ $ticket->created_at->format('d M Y') }}</td><td>
@can('tickets.view')
    @if($ticket->status === 'issued' && $ticket->qr_token_encrypted)
        <a class="btn btn-gold btn-sm" href="{{ route('admin.ticket-docs.invitation', $ticket) }}">Download ticket</a>
    @elseif($ticket->status === 'issued')
        @can('tickets.issue')
        <form method="POST" action="{{ route('admin.ticket-docs.reissue', $ticket) }}" onsubmit="return confirm('This will invalidate the QR on the ORIGINAL invitation. Continue and create a new downloadable QR?')">
            @csrf
            <input type="hidden" name="confirm" value="1">
            <button type="submit" class="btn btn-outline btn-sm">Enable re-download (new QR)</button>
        </form>
        @endcan
    @endif
@endcan
@can('tickets.issue')
    <a class="btn btn-outline btn-sm" href="{{ route('admin.ticket-guests.edit', $ticket->guest) }}">Edit guest</a>
@endcan
@can('tickets.revoke')
    @if($ticket->status === 'issued')
        <form method="POST" action="{{ route('admin.invitations.revoke', $ticket) }}" onsubmit="return confirm('Revoke this ticket permanently?')">
            @csrf
            <button type="submit" class="btn btn-quiet btn-sm">Revoke</button>
        </form>
    @endif
@endcan
</td></tr>
@empty<tr><td colspan="6"><div class="admin-empty"><strong>No invitations yet</strong><span>Issue your first complimentary invitation.</span></div></td></tr>@endforelse
</tbody></table></div>
<div class="inv-pages">{{ $tickets->links() }}</div>
</div>
@endsection
