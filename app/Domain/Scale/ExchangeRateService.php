<?php

namespace App\Domain\Scale;

class ExchangeRateService
{
    /**
     * Default static exchange rates relative to USD.
     */
    protected array $exchangeRates = [
        'USD' => 1.00,
        'EUR' => 0.92,
        'GBP' => 0.79,
        'JPY' => 155.20,
        'CAD' => 1.36,
        'AUD' => 1.51,
    ];

    /**
     * Convert minor amount from source currency to target currency.
     */
    public function convert(int $amountMinor, string $fromCurrency, string $toCurrency): int
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency   = strtoupper($toCurrency);

        if ($fromCurrency === $toCurrency) {
            return $amountMinor;
        }

        $fromRate = $this->exchangeRates[$fromCurrency] ?? 1.00;
        $toRate   = $this->exchangeRates[$toCurrency] ?? 1.00;

        $amountUsd = ($amountMinor / 100) / $fromRate;
        $convertedTarget = $amountUsd * $toRate;

        return (int) round($convertedTarget * 100);
    }

    /**
     * Format minor amount into human-readable currency string with symbol.
     */
    public function format(int $amountMinor, string $currency = 'USD'): string
    {
        $currency = strtoupper($currency);
        $amount = number_format($amountMinor / 100, 2);

        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CAD' => 'CA$',
            'AUD' => 'A$',
        ];

        $symbol = $symbols[$currency] ?? ($currency . ' ');
        return $symbol . $amount;
    }
}
