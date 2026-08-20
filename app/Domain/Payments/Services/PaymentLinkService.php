<?php

namespace App\Domain\Payments\Services;

use App\Domain\Folios\FolioLedgerService;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Support\Str;

class PaymentLinkService
{
    protected FolioLedgerService $ledgerService;
    protected PaymentGatewayRegistry $registry;

    public function __construct(FolioLedgerService $ledgerService, PaymentGatewayRegistry $registry)
    {
        $this->ledgerService = $ledgerService;
        $this->registry      = $registry;
    }

    /**
     * Generate a secure, unique payment link for a reservation.
     */
    public function generateLink(Reservation $reservation, int $amountMinor, string $channel = 'whatsapp'): array
    {
        $token = 'pay_' . Str::random(24);
        $url   = route('booking.portal.show', ['confirmationNumber' => $reservation->confirmation_number]) . "?pay_token={$token}";

        $messageText = "Hello {$reservation->primaryGuest->first_name}, please click to settle your reservation payment of {$reservation->currency} "
            . number_format($amountMinor / 100, 2) . ": {$url}";

        return [
            'token'         => $token,
            'url'           => $url,
            'amount_minor'  => $amountMinor,
            'currency'      => $reservation->currency,
            'channel'       => $channel,
            'message_text'  => $messageText,
            'expires_at'    => now()->addDays(3)->toIso8601String(),
        ];
    }
}
