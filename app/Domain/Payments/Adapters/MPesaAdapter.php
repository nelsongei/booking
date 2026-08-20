<?php

namespace App\Domain\Payments\Adapters;

use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MPesaAdapter implements PaymentGatewayInterface
{
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $shortcode;
    protected string $passkey;
    protected string $env;

    public function __construct(array $config = [])
    {
        $this->consumerKey    = $config['consumer_key'] ?? env('MPESA_CONSUMER_KEY', 'demo_key');
        $this->consumerSecret = $config['consumer_secret'] ?? env('MPESA_CONSUMER_SECRET', 'demo_secret');
        $this->shortcode      = $config['shortcode'] ?? env('MPESA_SHORTCODE', '174379');
        $this->passkey        = $config['passkey'] ?? env('MPESA_PASSKEY', 'demo_passkey');
        $this->env            = $config['env'] ?? env('MPESA_ENV', 'sandbox');
    }

    public function getProviderName(): string
    {
        return 'mpesa';
    }

    public function stkPush(string $phoneNumber, int $amountMinor, string $accountReference, string $description): array
    {
        // Format phone number to 2547XXXXXXXX
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);
        $amountInShillings = (int) ceil($amountMinor / 100);

        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);

        // Simulation / Sandbox STK Push payload
        $checkoutRequestId = 'ws_CO_' . $timestamp . '_' . rand(1000, 9999);

        return [
            'success'               => true,
            'provider'              => 'mpesa',
            'checkout_request_id'   => $checkoutRequestId,
            'merchant_request_id'   => 'MR_' . Str::random(8),
            'response_code'         => '0',
            'response_description'  => 'Success. Request accepted for processing',
            'customer_message'      => "STK Push sent to {$formattedPhone}. Please enter M-Pesa PIN on your phone.",
            'amount_shillings'      => $amountInShillings,
            'formatted_phone'       => $formattedPhone,
        ];
    }

    public function authorize(array $params): array
    {
        return $this->stkPush(
            $params['phone'],
            $params['amount_minor'],
            $params['reference'] ?? 'BOOKING',
            $params['description'] ?? 'Hotel Reservation Payment'
        );
    }

    public function capture(string $transactionId, int $amountMinor): array
    {
        return [
            'success'        => true,
            'transaction_id' => $transactionId,
            'status'         => 'captured',
        ];
    }

    public function refund(string $transactionId, int $amountMinor, string $reason): array
    {
        return [
            'success'         => true,
            'transaction_id'  => $transactionId,
            'refund_id'       => 'MP_REF_' . Str::random(10),
            'status'          => 'refunded',
            'amount_refunded' => $amountMinor,
        ];
    }

    public function verify(string $transactionId): array
    {
        return [
            'success'        => true,
            'transaction_id' => $transactionId,
            'status'         => 'completed',
            'result_code'    => 0,
        ];
    }

    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1);
        }
        if (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            return '254' . $phone;
        }
        return $phone;
    }
}
