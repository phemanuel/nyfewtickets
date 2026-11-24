<?php
namespace App\Services\Tickets;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class PremiumTicketPdf
{
    public function render($tickets)
    {
        $items = collect($tickets)->values();
        abort_if($items->isEmpty(), 404);
        $pages = $items->map(function (Ticket $ticket) {
            abort_unless($ticket->status === 'issued', 403, 'Only valid issued tickets can be downloaded.');
            abort_unless(!empty($ticket->qr_token_encrypted), 409, 'This invitation needs a QR reissue before it can be downloaded again.');
            $ticket->loadMissing(['event', 'category', 'guest']);
            try {
                $token = Crypt::decryptString($ticket->qr_token_encrypted);
            } catch (\Throwable $e) {
                throw new RuntimeException('Could not decrypt ticket token. Check that APP_KEY has not changed.', 0, $e);
            }
            abort_unless(hash_equals($ticket->token_hash, hash('sha256', $token)), 409, 'Ticket token does not match database record.');
            $qr = (new PngWriter())->write(new QrCode(route('tickets.verify', ['token'=>$token]), size: 330, margin: 12));
            return ['ticket'=>$ticket, 'qrData'=>base64_encode($qr->getString())];
        });
        return Pdf::loadView('tickets.premium-document', ['pages'=>$pages])->setPaper('a4','landscape');
    }
}
