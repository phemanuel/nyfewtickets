<?php
namespace App\Http\Controllers;

use App\Models\TicketAccessLink;
use App\Models\TicketOrder;
use App\Services\Tickets\TicketDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class TicketRecoveryController extends Controller
{
    public function index() { return view('ticket-recovery.index'); }

    public function requestLink(Request $request, TicketDeliveryService $delivery)
    {
        $data = $request->validate([
            'reference'=>['required','string','max:80'],
            'email'=>['required','email','max:190'],
        ]);
        $email = strtolower(trim($data['email']));
        $key = 'nyfew-recovery:'.hash('sha256', $email);
        if (!RateLimiter::tooManyAttempts($key, 3)) {
            RateLimiter::hit($key, 3600);
            $order = TicketOrder::where('reference', strtoupper(trim($data['reference'])))
                ->where('buyer_email', $email)->where('status','paid')->first();
            if ($order) {
                try { $delivery->queueAccessEmail($order); }
                catch (\Throwable $e) { report($e); }
            }
        }
        // Same response whether an order exists or not: no email enumeration.
        return back()->with('success', 'If those details match a paid order, a secure link will be emailed. Check your inbox and spam folder.');
    }

    public function open(string $token)
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{80}$/', $token), 404);
        $link = TicketAccessLink::where('token_hash', hash('sha256', $token))->first();
        if (!$link || $link->used_at !== null || !$link->expires_at || !$link->expires_at->isFuture()) {
            return response()->view('ticket-recovery.expired', [], 410);
        }
        abort_unless($link->order && $link->order->status === 'paid', 403);
        return view('ticket-recovery.open', compact('token'));
    }

    public function consume(Request $request, string $token)
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{80}$/', $token), 404);
        $orderId = DB::transaction(function () use ($token) {
            $link = TicketAccessLink::where('token_hash', hash('sha256',$token))->lockForUpdate()->first();
            if (!$link || $link->used_at !== null || !$link->expires_at || !$link->expires_at->isFuture()) {
                return null;
            }
            $order = TicketOrder::whereKey($link->ticket_order_id)->firstOrFail();
            abort_unless($order->status === 'paid', 403);
            // New access links remain valid until expiry. Legacy consumed links stay invalid.
            return $order->id;
        });
        if ($orderId === null) {
            return response()->view('ticket-recovery.expired', [], 410);
        }
        $order = TicketOrder::findOrFail($orderId);
        $request->session()->regenerate();
        $request->session()->put('nyfew_recovered_order.'.$order->id, true);
        return redirect()->route('sales.receipt', $order->reference);
    }
}
