@extends('layouts.admin')
@section('heading','Order Details')
@section('content')
<div style="margin-bottom:22px"><a href="{{ route('admin.ticket-sales.index') }}" style="color:#d7b768">← Back to sales</a></div>
<div class="card">
 <h2>Order {{ $order->reference }}</h2>
 <p><strong>Buyer:</strong> {{ $order->buyer_name }} · {{ $order->buyer_email }}</p>
 <p><strong>Event:</strong> {{ $order->event->title }}</p>
 <p><strong>Status:</strong> {{ strtoupper(str_replace('_', ' ', $order->status)) }} · <strong>Total:</strong> ₦{{ number_format($order->amount_kobo/100,2) }}</p>
 @if(in_array($order->status,['paid','paid_unfulfilled'],true))
  <a class="btn btn-outline btn-sm" href="{{ route('admin.ticket-docs.receipt',$order) }}">Download receipt</a>
 @endif
 @if($order->status === 'paid')
  <form method="POST" action="{{ route('admin.ticket-delivery.resend', $order) }}" style="display:inline-block;margin:8px 8px 8px 0">@csrf<button class="btn btn-outline btn-sm" type="submit">Email ticket access link again</button></form>
  <a class="btn btn-gold btn-sm" href="{{ route('admin.ticket-docs.all',$order) }}">Download all tickets (PDF)</a>
 @endif
</div>
<div class="card" style="margin-top:18px"><h2>Attendees & tickets</h2>
<div style="overflow:auto"><table><thead><tr><th>Guest</th><th>Category</th><th>Ticket number</th><th>Download</th></tr></thead><tbody>
@foreach($order->attendees as $person)
<tr><td>{{ $person->full_name }}</td><td>{{ $person->category->name }}</td>
<td>{{ $person->ticket?->number ?: 'Awaiting issuance' }}</td>
<td>@if($order->status === 'paid' && $person->ticket && $person->ticket->status === 'issued')
<a class="btn btn-outline btn-sm" href="{{ route('admin.ticket-docs.one', [$order,$person->ticket]) }}">Download ticket</a>
@else<span>Unavailable</span>@endif</td></tr>
@endforeach
</tbody></table></div></div>
@endsection
