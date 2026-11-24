<?php

namespace App\Mail;

use App\Models\TicketOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $buyerName,
        public string $eventTitle,
        public string $reference,
        public string $accessUrl
    ) {}

    public function build(): self
    {
        $order = TicketOrder::with('event')->where('reference', $this->reference)->first();
        $posterPath = $order?->event?->posterFilePath();

        return $this->subject('Your NYFEW tickets are ready — '.$this->reference)
            ->view('ticket-recovery.email')
            ->with(['eventPosterPath' => $posterPath]);
    }
}
