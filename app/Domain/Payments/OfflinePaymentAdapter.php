<?php

namespace App\Domain\Payments;

use App\Infrastructure\Persistence\Property;
use Illuminate\Support\Str;

class OfflinePaymentAdapter implements PaymentGatewayInterface
{
    public function createPaymentIntent(
        Property $property,
        int $amountMinor,
        string $currency,
        array $metadata = []
    ): array {
        return [
            'success'           => true,
            'payment_intent_id' => 'off_' . Str::lower((string) Str::ulid()),
            'client_secret'     => 'off_secret_' . Str::lower((string) Str::ulid()),
            'status'            => 'succeeded',
            'amount_minor'      => $amountMinor,
            'currency'          => strtolower($currency),
            'provider'          => $metadata['provider'] ?? 'cash',
        ];
    }

    public function capturePayment(string $paymentIntentId): array
    {
        return [
            'success'           => true,
            'payment_intent_id' => $paymentIntentId,
            'status'            => 'succeeded',
            'captured_at'       => now()->toIso8601String(),
        ];
    }

    public function refundPayment(string $paymentIntentId, int $amountMinor, string $reason = 'requested_by_customer'): array
    {
        return [
            'success'   => true,
            'refund_id' => 'off_ref_' . Str::lower((string) Str::ulid()),
            'amount'    => $amountMinor,
            'status'    => 'succeeded',
        ];
    }
}
