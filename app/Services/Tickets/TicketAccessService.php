<?php
namespace App\Services\Tickets;

use App\Models\TicketAccessLink;
use App\Models\TicketOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketAccessService
{
    public function createLink(TicketOrder $order, int $minutes = 1440): string
    {
        $token = Str::random(80);
        TicketAccessLink::create([
            'ticket_order_id'=>$order->id,
            'token_hash'=>hash('sha256', $token),
            'expires_at'=>now()->addMinutes($minutes),
        ]);
        return route('ticket-recovery.open', ['token'=>$token]);
    }

    public function canRead(Request $request, TicketOrder $order): bool
    {
        $original = $request->session()->get('nyfew_order_access.'.$order->reference);
        if (is_string($original) && hash_equals($order->access_hash, hash('sha256', $original))) return true;
        return $order->status === 'paid' && $request->session()->get('nyfew_recovered_order.'.$order->id) === true;
    }
}
