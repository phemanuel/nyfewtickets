<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Services\Tickets\PremiumTicketPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class TicketDocumentController extends Controller
{
    private function paidTickets(TicketOrder $order)
    {
        abort_unless($order->status === 'paid', 403, 'Tickets are available only for fulfilled paid orders.');
        $order->load(['event', 'attendees.ticket']);
        $tickets = $order->attendees->pluck('ticket')->filter()->values();
        abort_unless($tickets->count() === $order->attendees->count(), 409, 'Some tickets have not yet been issued.');
        abort_unless($tickets->every(fn ($t) => $t->kind === 'paid' && $t->status === 'issued'), 403);
        return $tickets;
    }

    public function order(TicketOrder $order)
    {
        $this->paidTickets($order);
        $order->load('attendees.category');
        return view('admin.sales.show', compact('order'));
    }

    public function one(TicketOrder $order, Ticket $ticket, PremiumTicketPdf $pdf)
    {
        abort_unless($this->paidTickets($order)->contains('id', $ticket->id), 404);
        return $pdf->render([$ticket])->download($ticket->number.'.pdf');
    }

    public function all(TicketOrder $order, PremiumTicketPdf $pdf)
    {
        return $pdf->render($this->paidTickets($order))->download('NYFEW-'.$order->reference.'-tickets.pdf');
    }

    public function receipt(TicketOrder $order)
    {
        abort_unless(in_array($order->status, ['paid','paid_unfulfilled'], true), 403);
        $order->load(['event','attendees.category']);
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('tickets.order-receipt', compact('order'))
            ->setPaper('a4','portrait')->download('NYFEW-Receipt-'.$order->reference.'.pdf');
    }

    public function invitation(Ticket $ticket, PremiumTicketPdf $pdf)
    {
        abort_unless($ticket->kind === 'invitation' && $ticket->status === 'issued', 404);
        return $pdf->render([$ticket])->download($ticket->number.'.pdf');
    }

    /** Legacy invitation tokens were hashed only. An explicit POST rotates the token and invalidates its former QR. */
    public function reissue(Request $request, Ticket $ticket)
    {
        abort_unless($ticket->kind === 'invitation' && $ticket->status === 'issued', 404);
        $request->validate(['confirm' => ['required','accepted']]);
        \Illuminate\Support\Facades\DB::transaction(function () use ($ticket) {
            $locked = Ticket::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->kind === 'invitation' && $locked->status === 'issued', 404);
            abort_if($locked->qr_token_encrypted, 409, 'This ticket already supports re-download.');
            $token = Str::random(80);
            $locked->forceFill([
                'token_hash'=>hash('sha256',$token),
                'qr_token_encrypted'=>Crypt::encryptString($token),
            ])->save();
        });
        return redirect()->route('admin.ticket-docs.invitation', $ticket)
            ->with('success','A new QR was issued. The original QR is now invalid; deliver the replacement ticket to the guest.');
    }
}
