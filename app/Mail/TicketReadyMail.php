<?php
namespace App\Mail;

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
        return $this->subject('Your NYFEW tickets are ready — '.$this->reference)
            ->view('ticket-recovery.email');
    }
}
