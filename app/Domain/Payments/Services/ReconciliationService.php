<?php

namespace App\Domain\Payments\Services;

use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Support\Facades\DB;

class ReconciliationService
{
    /**
     * Process incoming raw payment payload into reconciled transaction or suspense entry.
     */
    public function reconcileIncomingPayment(
        Property $property,
        string $provider,
        string $providerPaymentId,
        int $amountMinor,
        ?string $accountReference,
        ?string $payerPhone
    ): array {
        // Attempt match by confirmation number or phone number
        $reservation = null;

        if ($accountReference) {
            $reservation = Reservation::where('property_id', $property->id)
                ->where('confirmation_number', trim($accountReference))
                ->first();
        }

        if (!$reservation && $payerPhone) {
            $reservation = Reservation::where('property_id', $property->id)
                ->whereHas('primaryGuest', fn($q) => $q->where('phone', 'like', "%{$payerPhone}%"))
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->first();
        }

        if ($reservation) {
            // Record matched payment
            $paymentId = DB::table('payments')->insertGetId([
                'ulid'                 => (string) \Illuminate\Support\Str::ulid(),
                'organization_id'      => $property->organization_id,
                'property_id'          => $property->id,
                'reservation_id'       => $reservation->id,
                'provider'             => $provider,
                'provider_payment_id'  => $providerPaymentId,
                'amount_minor'         => $amountMinor,
                'currency'             => $property->currency,
                'status'               => 'captured',
                'payment_method'       => 'mobile_money',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            return [
                'status'         => 'reconciled',
                'payment_id'     => $paymentId,
                'reservation_id' => $reservation->id,
                'confirmation'   => $reservation->confirmation_number,
            ];
        }

        // Unmatched payment placed into Suspense Queue
        return [
            'status'             => 'suspense',
            'provider'           => $provider,
            'provider_payment_id'=> $providerPaymentId,
            'amount_minor'       => $amountMinor,
            'reason'             => 'No matching reservation found for account reference or phone.',
        ];
    }
}
