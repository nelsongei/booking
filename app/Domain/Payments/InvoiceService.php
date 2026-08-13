<?php

namespace App\Domain\Payments;

use App\Infrastructure\Persistence\Invoice;
use App\Infrastructure\Persistence\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate or update a PDF invoice for a reservation.
     */
    public function generateForReservation(Reservation $reservation, string $type = 'invoice'): Invoice
    {
        $property = $reservation->property;
        $guest    = $reservation->primaryGuest;

        $invoiceNumber = 'INV-' . date('Ym') . '-' . strtoupper(substr((string) Str::ulid(), -6));

        // Format line items
        $lineItems = [
            [
                'description'  => 'Accommodation Stay (' . $reservation->nights . ' nights, ' . $reservation->adults . ' adults)',
                'amount_minor' => $reservation->subtotal_minor,
            ]
        ];

        if ($reservation->tax_minor > 0) {
            $lineItems[] = [
                'description'  => 'Taxes & Fees',
                'amount_minor' => $reservation->tax_minor,
            ];
        }

        // Render PDF HTML template
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice_number' => $invoiceNumber,
            'reservation'    => $reservation,
            'property'       => $property,
            'guest'          => $guest,
            'line_items'     => $lineItems,
            'issued_at'      => now()->format('F d, Y'),
        ]);

        $pdfDirectory = 'invoices';
        if (!Storage::disk('local')->exists($pdfDirectory)) {
            Storage::disk('local')->makeDirectory($pdfDirectory);
        }

        $pdfPath = $pdfDirectory . '/' . $invoiceNumber . '.pdf';
        Storage::disk('local')->put($pdfPath, $pdf->output());

        return Invoice::create([
            'ulid'             => (string) Str::ulid(),
            'reservation_id'   => $reservation->id,
            'property_id'      => $property->id,
            'invoice_number'   => $invoiceNumber,
            'type'             => $type,
            'line_items'       => $lineItems,
            'subtotal_minor'   => $reservation->subtotal_minor,
            'tax_minor'        => $reservation->tax_minor,
            'total_minor'      => $reservation->total_minor,
            'currency'         => $reservation->currency,
            'pdf_path'         => $pdfPath,
            'issued_at'        => now(),
        ]);
    }
}
