<?php

namespace App\Domain\Payments;

use App\Infrastructure\Persistence\Property;

interface PaymentGatewayInterface
{
    /**
     * Create a payment intent / checkout transaction session.
     */
    public function createPaymentIntent(
        Property $property,
        int $amountMinor,
        string $currency,
        array $metadata = []
    ): array;

    /**
     * Capture / confirm an authorized payment intent.
     */
    public function capturePayment(string $paymentIntentId): array;

    /**
     * Refund a processed payment.
     */
    public function refundPayment(string $paymentIntentId, int $amountMinor, string $reason = 'requested_by_customer'): array;
}
