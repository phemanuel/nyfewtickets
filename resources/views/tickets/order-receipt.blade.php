<!doctype html><html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#222;padding:35px}h1{color:#a37f31}table{border-collapse:collapse;width:100%;margin-top:20px}td,th{padding:10px;border-bottom:1px solid #ddd;text-align:left}.total{font-weight:bold;font-size:18px;text-align:right;margin-top:20px}
</style></head><body><h1>NYFEW Tickets</h1><h2>Payment Receipt</h2>
<p>Order: {{ $order->reference }}</p><p>Event: {{ $order->event->title }}</p>
<p>Buyer: {{ $order->buyer_name }} ({{ $order->buyer_email }})</p>
<p>Status: {{ strtoupper(str_replace('_',' ',$order->status)) }}</p><p>Paid at: {{ $order->paid_at?->format('d M Y, h:i A') ?? 'Pending' }}</p>
<p>Paystack transaction ID: {{ $order->paystack_transaction_id ?: 'Not available' }}</p>
<table><tr><th>Attendee</th><th>Category</th><th>Price</th></tr>
@foreach($order->attendees as $person)<tr><td>{{ $person->full_name }}</td><td>{{ $person->category->name }}</td><td>NGN {{ number_format($person->unit_price_kobo / 100, 2) }}</td></tr>@endforeach</table>
<div class="total">TOTAL: NGN {{ number_format($order->amount_kobo / 100, 2) }}</div>
<p style="font-size:11px;margin-top:30px">This receipt is proof of payment, not an admission ticket. Present the separate QR ticket at the venue.</p></body></html>
