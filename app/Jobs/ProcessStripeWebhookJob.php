<?php

namespace App\Jobs;

use App\Domain\Payments\InvoiceService;
use App\Infrastructure\Persistence\Payment;
use App\Infrastructure\Persistence\Reservation;
use App\Mail\PaymentReceiptMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProcessStripeWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $eventData;

    public function __construct(array $eventData)
    {
        $this->eventData = $eventData;
    }

    public function handle(InvoiceService $invoiceService): void
    {
        $type = $this->eventData['type'] ?? '';
        $object = $this->eventData['data'] ?? [];

        if (in_array($type, ['payment_intent.succeeded', 'charge.succeeded'])) {
            $paymentIntentId = $object['id'] ?? ($object['payment_intent'] ?? null);
            $amount          = (int) ($object['amount'] ?? ($object['amount_received'] ?? 0));
            $currency        = strtoupper($object['currency'] ?? 'USD');
            $metadata        = $object['metadata'] ?? [];

            $reservationId = $metadata['reservation_id'] ?? null;
            $reservation   = null;

            if ($reservationId) {
                $reservation = Reservation::find($reservationId);
            }

            $payment = Payment::create([
                'ulid'                     => (string) Str::ulid(),
                'reservation_id'           => $reservation?->id,
                'property_id'              => $reservation?->property_id ?? $metadata['property_id'] ?? 1,
                'provider'                 => 'stripe',
                'provider_payment_id'      => $paymentIntentId,
                'provider_payment_method'  => $object['payment_method_types'][0] ?? 'card',
                'amount_minor'             => $amount,
                'currency'                 => $currency,
                'status'                   => 'captured',
                'type'                     => 'capture',
                'provider_metadata'        => $object,
                'captured_at'              => now(),
            ]);

            if ($reservation) {
                // Update reservation paid balance
                $newBalance = max(0, $reservation->balance_minor - $amount);
                $reservation->update([
                    'deposit_minor' => $reservation->deposit_minor + $amount,
                    'balance_minor' => $newBalance,
                ]);

                // Generate invoice
                $invoice = $invoiceService->generateForReservation($reservation, 'receipt');

                // Send payment receipt mail
                if ($reservation->primaryGuest?->email) {
                    Mail::to($reservation->primaryGuest->email)->send(new PaymentReceiptMail($reservation, $payment, $invoice));
                }
            }
        }
    }
}
