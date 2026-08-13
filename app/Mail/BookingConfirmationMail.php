<?php

namespace App\Mail;

use App\Infrastructure\Persistence\Invoice;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public ?Invoice $invoice;

    public function __construct(Reservation $reservation, ?Invoice $invoice = null)
    {
        $this->reservation = $reservation;
        $this->invoice     = $invoice;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmation #' . $this->reservation->confirmation_number . ' — ' . $this->reservation->property->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking_confirmation',
            with: [
                'reservation' => $this->reservation,
                'property'    => $this->reservation->property,
                'guest'       => $this->reservation->primaryGuest,
            ],
        );
    }

    public function attachments(): array
    {
        if ($this->invoice && Storage::disk('local')->exists($this->invoice->pdf_path)) {
            return [
                Attachment::fromPath(Storage::disk('local')->path($this->invoice->pdf_path))
                    ->as($this->invoice->invoice_number . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
