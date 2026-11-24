@extends('layouts.admin')
@section('heading','Ticket Sales')
@section('content')
<div style="color:#d7b768;letter-spacing:2px;font-size:12px;margin-bottom:15px">NYFEW / PAYMENT OPERATIONS</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:24px">
    <div class="card"><div>Confirmed orders</div><h2>{{ number_format($paidCount) }}</h2></div>
    <div class="card"><div>Gross verified payments</div><h2>₦{{ number_format($revenue/100,2) }}</h2></div>
    <div class="card"><div>Requires attention</div><h2>{{ number_format($attentionCount) }}</h2></div>
</div>
<div class="card"><h2>Orders</h2>
<form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px">
    <select name="event_id" aria-label="Event"><option value="">All events</option>@foreach($events as $event)<option value="{{ $event->id }}" @selected(request('event_id')==$event->id)>{{ $event->title }}</option>@endforeach</select>
    <select name="status" aria-label="Payment status"><option value="">All statuses</option>@foreach(['pending','paid','paid_unfulfilled','failed'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>@endforeach</select>
    <button class="btn btn-gold" type="submit">Filter</button>
</form>
<div style="overflow:auto"><table><thead><tr><th>Reference</th><th>Event</th><th>Buyer</th><th>Attendees</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($orders as $order)
<tr><td>{{ $order->reference }}</td><td>{{ $order->event->title }}</td><td>{{ $order->buyer_name }}<br>{{ $order->buyer_email }}</td><td>{{ $order->attendees_count }}</td><td>₦{{ number_format($order->amount_kobo/100,2) }}</td><td>{{ strtoupper(str_replace('_',' ',$order->status)) }}</td><td><a class="btn btn-outline btn-sm" href="{{ route('admin.ticket-docs.order', $order) }}">View / Download</a></td></tr>
@empty<tr><td colspan="7">No orders found.</td></tr>@endforelse
</tbody></table></div>{{ $orders->links() }}</div>
@endsection
