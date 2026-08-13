<?php

namespace App\Mail;

use App\Infrastructure\Persistence\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancellationNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public string $reason;

    public function __construct(Reservation $reservation, string $reason = '')
    {
        $this->reservation = $reservation;
        $this->reason      = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reservation Cancellation Notice #' . $this->reservation->confirmation_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cancellation_notice',
            with: [
                'reservation' => $this->reservation,
                'property'    => $this->reservation->property,
                'guest'       => $this->reservation->primaryGuest,
                'reason'      => $this->reason,
            ],
        );
    }
}
