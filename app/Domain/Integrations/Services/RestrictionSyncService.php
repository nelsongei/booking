<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Integrations\Connectors\BookingComConnector;
use App\Infrastructure\Persistence\Property;

class RestrictionSyncService
{
    protected BookingComConnector $bookingCom;

    public function __construct(BookingComConnector $bookingCom)
    {
        $this->bookingCom = $bookingCom;
    }

    public function updateRestrictions(
        Property $property,
        int $roomTypeId,
        string $startDate,
        string $endDate,
        array $restrictions
    ): bool {
        // Restrictions structure: ['min_stay' => 2, 'cta' => true, 'stop_sell' => false]
        $payload = [
            'room_type_id' => $roomTypeId,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'restrictions' => $restrictions,
        ];

        return $this->bookingCom->pushRestrictions($property, $payload);
    }
}
