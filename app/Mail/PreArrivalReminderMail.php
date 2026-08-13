<?php

namespace App\Mail;

use App\Infrastructure\Persistence\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PreArrivalReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pre-Arrival Information for Your Upcoming Stay at ' . $this->reservation->property->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pre_arrival_reminder',
            with: [
                'reservation' => $this->reservation,
                'property'    => $this->reservation->property,
                'guest'       => $this->reservation->primaryGuest,
            ],
        );
    }
}
