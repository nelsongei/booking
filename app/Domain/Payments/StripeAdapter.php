<?php

namespace App\Domain\Payments;

use App\Infrastructure\Persistence\Property;
use Stripe\StripeClient;

class StripeAdapter implements PaymentGatewayInterface
{
    protected ?StripeClient $stripe = null;

    public function __construct(?string $secretKey = null)
    {
        $key = $secretKey ?? config('services.stripe.secret', env('STRIPE_SECRET'));
        if (!empty($key)) {
            $this->stripe = new StripeClient($key);
        }
    }

    public function createPaymentIntent(
        Property $property,
        int $amountMinor,
        string $currency,
        array $metadata = []
    ): array {
        if (!$this->stripe) {
            // Fallback for environment without live Stripe key
            return [
                'success'           => true,
                'payment_intent_id' => 'pi_mock_' . bin2hex(random_bytes(8)),
                'client_secret'     => 'pi_mock_secret_' . bin2hex(random_bytes(8)),
                'status'            => 'requires_payment_method',
                'amount_minor'      => $amountMinor,
                'currency'          => strtolower($currency),
                'mock'              => true,
            ];
        }

        try {
            $intent = $this->stripe->paymentIntents->create([
                'amount'   => $amountMinor,
                'currency' => strtolower($currency),
                'metadata' => array_merge(['property_id' => $property->id], $metadata),
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return [
                'success'           => true,
                'payment_intent_id' => $intent->id,
                'client_secret'     => $intent->client_secret,
                'status'            => $intent->status,
                'amount_minor'      => $intent->amount,
                'currency'          => $intent->currency,
                'mock'              => false,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    public function capturePayment(string $paymentIntentId): array
    {
        if (!$this->stripe || str_starts_with($paymentIntentId, 'pi_mock_')) {
            return [
                'success'           => true,
                'payment_intent_id' => $paymentIntentId,
                'status'            => 'succeeded',
                'captured_at'       => now()->toIso8601String(),
                'mock'              => true,
            ];
        }

        try {
            $intent = $this->stripe->paymentIntents->capture($paymentIntentId);
            return [
                'success'           => true,
                'payment_intent_id' => $intent->id,
                'status'            => $intent->status,
                'captured_at'       => now()->toIso8601String(),
                'mock'              => false,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    public function refundPayment(string $paymentIntentId, int $amountMinor, string $reason = 'requested_by_customer'): array
    {
        if (!$this->stripe || str_starts_with($paymentIntentId, 'pi_mock_')) {
            return [
                'success'   => true,
                'refund_id' => 're_mock_' . bin2hex(random_bytes(8)),
                'amount'    => $amountMinor,
                'status'    => 'succeeded',
                'mock'      => true,
            ];
        }

        try {
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $paymentIntentId,
                'amount'         => $amountMinor,
                'reason'         => in_array($reason, ['duplicate', 'fraudulent', 'requested_by_customer']) ? $reason : 'requested_by_customer',
            ]);

            return [
                'success'   => true,
                'refund_id' => $refund->id,
                'amount'    => $refund->amount,
                'status'    => $refund->status,
                'mock'      => false,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
