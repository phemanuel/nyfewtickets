<?php
namespace App\Http\Controllers;

use App\Models\{Event,TicketOrder};
use App\Services\Tickets\{PaystackClient,TicketSaleService,TicketAccessService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\Ticket;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TicketSaleController extends Controller
{
    public function show(Event $event)
    {
        abort_unless($event->status === 'published', 404);
        $categories = $event->ticketCategories()->where('type','paid')->where('is_active',true)->orderBy('sort_order')->get();
        return view('ticket-sales.checkout', compact('event','categories'));
    }

    public function checkout(Request $request, Event $event, TicketSaleService $sales, PaystackClient $paystack)
    {
        abort_unless(config('nyfew_paystack.enabled'), 503, 'Ticket sales are not enabled.');
        abort_unless($event->status === 'published', 404);
        $data = $request->validate([
            'buyer.name'=>'required|string|max:180','buyer.email'=>'required|email|max:190',
            'buyer.phone'=>'nullable|string|max:40',
            'attendees'=>'required|array|min:1|max:10',
            'attendees.*.category_id'=>'required|integer',
            'attendees.*.full_name'=>'required|string|max:180',
            'attendees.*.email'=>'nullable|email|max:190',
            'attendees.*.phone'=>'nullable|string|max:40',
        ]);
        [$order, $access] = $sales->reserve($event, $data['buyer'], $data['attendees']);
        $request->session()->put('nyfew_order_access.'.$order->reference, $access);
        try {
            $url = $paystack->initialize($order->buyer_email, $order->amount_kobo, $order->reference, route('sales.callback'));
        } catch (RuntimeException $e) {
            $order->update(['status'=>'failed']);
            return back()->withInput()->withErrors(['payment'=>'Unable to start payment. Please try again.']);
        }
        return redirect()->away($url);
    }

    public function callback(Request $request, PaystackClient $paystack, TicketSaleService $sales)
    {
        $reference = (string) $request->query('reference','');
        abort_unless(preg_match('/^NYFT-[A-Z0-9]{24}$/', $reference), 404);
        $order = TicketOrder::where('reference',$reference)->firstOrFail();
        $access = $request->session()->get('nyfew_order_access.'.$reference);
        abort_unless(is_string($access) && hash_equals($order->access_hash, hash('sha256',$access)), 403);
        try {
            $verified = $paystack->verify($reference);
            if (($verified['status'] ?? null) === 'success') $sales->confirm($reference, $verified);
        } catch (RuntimeException $e) {
            // Display pending status; a valid webhook can complete the order later.
            report($e);
        }
        return redirect()->route('sales.receipt', ['reference'=>$reference]);
    }

    public function receipt(Request $request, string $reference)
    {
        $order = TicketOrder::where('reference',$reference)->with(['event','attendees.ticket','attendees.category'])->firstOrFail();
        $access = $request->session()->get('nyfew_order_access.'.$reference);
        abort_unless(app(TicketAccessService::class)->canRead($request, $order), 403);
        return view('ticket-sales.receipt', compact('order'));
    }

    public function download(Request $request, string $reference, Ticket $ticket)
    {
        $order = TicketOrder::where('reference',$reference)->firstOrFail();
        $access = $request->session()->get('nyfew_order_access.'.$reference);
        abort_unless($order->status === 'paid' && app(TicketAccessService::class)->canRead($request, $order), 403);
        abort_unless($order->attendees()->where('ticket_id', $ticket->id)->exists(), 404);
        abort_unless($ticket->kind === 'paid' && $ticket->qr_token_encrypted, 404);
        $ticket->load(['event','guest','category']);
        $token = Crypt::decryptString($ticket->qr_token_encrypted);
        abort_unless(hash_equals($ticket->token_hash, hash('sha256',$token)), 404);
        $url = route('tickets.verify', ['token'=>$token]);
        $qr = (new PngWriter())->write(new QrCode($url, size: 270, margin: 12));
        $qrData = base64_encode($qr->getString());
        $pdf = Pdf::loadView('ticket-sales.pdf', compact('ticket','qrData'))->setPaper('a4','landscape');
        return $pdf->download($ticket->number.'.pdf');
    }

    public function webhook(Request $request, PaystackClient $paystack, TicketSaleService $sales)
    {
        if (!$paystack->validWebhook($request->getContent(), $request->header('x-paystack-signature'))) {
            return response('Invalid signature', 401);
        }
        $payload = $request->json()->all();
        if (($payload['event'] ?? '') !== 'charge.success') return response('Ignored', 200);
        $reference = data_get($payload,'data.reference');
        if (!is_string($reference) || !preg_match('/^NYFT-[A-Z0-9]{24}$/', $reference)) return response('Ignored', 200);
        if (!TicketOrder::where('reference',$reference)->exists()) return response('Ignored', 200);
        try {
            // Always verify against Paystack's API instead of trusting webhook data alone.
            $verified = $paystack->verify($reference);
            $sales->confirm($reference, $verified);
        } catch (\Throwable $e) {
            report($e);
            return response('Retry later', 503);
        }
        return response('OK', 200);
    }
}
