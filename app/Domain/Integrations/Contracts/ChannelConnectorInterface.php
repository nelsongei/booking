<?php

namespace App\Domain\Integrations\Contracts;

use App\Infrastructure\Persistence\Property;

interface ChannelConnectorInterface
{
    public function getChannelCode(): string;

    public function pushInventory(Property $property, array $matrix): bool;

    public function pushRestrictions(Property $property, array $restrictions): bool;

    public function fetchReservations(Property $property): array;

    public function acknowledgeReservation(string $otaReservationId): bool;
}
