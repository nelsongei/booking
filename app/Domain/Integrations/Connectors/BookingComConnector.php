<?php

namespace App\Domain\Integrations\Connectors;

use App\Domain\Integrations\Contracts\ChannelConnectorInterface;
use App\Infrastructure\Persistence\Property;

class BookingComConnector implements ChannelConnectorInterface
{
    public function getChannelCode(): string
    {
        return 'booking_com';
    }

    public function pushInventory(Property $property, array $matrix): bool
    {
        // Simulated B.XML OTA_HotelAvailNotifRQ payload transmission
        return true;
    }

    public function pushRestrictions(Property $property, array $restrictions): bool
    {
        // Pushes MinStay, MaxStay, CTA, CTD, StopSell flags
        return true;
    }

    public function fetchReservations(Property $property): array
    {
        return [];
    }

    public function acknowledgeReservation(string $otaReservationId): bool
    {
        return true;
    }
}
