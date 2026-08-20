<?php

namespace App\Domain\Payments\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Get the unique gateway provider name identifier.
     */
    public function getProviderName(): string;

    /**
     * Initiate an M-Pesa / Mobile Money STK Push prompt to guest device.
     */
    public function stkPush(string $phoneNumber, int $amountMinor, string $accountReference, string $description): array;

    /**
     * Authorize or charge a payment.
     */
    public function authorize(array $params): array;

    /**
     * Capture a pre-authorized payment.
     */
    public function capture(string $transactionId, int $amountMinor): array;

    /**
     * Execute a full or partial refund.
     */
    public function refund(string $transactionId, int $amountMinor, string $reason): array;

    /**
     * Verify payment status directly with payment provider.
     */
    public function verify(string $transactionId): array;
}
