<?php

namespace App\Domain\Payments\Services;

use App\Domain\Payments\Adapters\MPesaAdapter;
use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Persistence\Property;

class PaymentGatewayRegistry
{
    protected array $gateways = [];

    public function __construct()
    {
        $this->register(new MPesaAdapter());
        $this->register(new \App\Domain\Payments\Adapters\StripeAdapter());
    }

    public function register(PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$gateway->getProviderName()] = $gateway;
    }

    public function get(string $provider): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$provider])) {
            throw new \InvalidArgumentException("Payment gateway provider '{$provider}' is not registered.");
        }

        return $this->gateways[$provider];
    }

    public function getAvailableGatewaysForProperty(Property $property): array
    {
        $currency = strtoupper($property->currency ?? 'USD');

        $available = ['stripe'];

        if (in_array($currency, ['KES', 'TZS', 'UGX', 'RWF'], true)) {
            $available[] = 'mpesa';
            $available[] = 'mtn_momo';
        }

        return $available;
    }
}
