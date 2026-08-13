<?php

namespace App\Domain\Inventory;

use App\Infrastructure\Persistence\InventoryDay;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
    /**
     * Check availability for a property and date range with FOR UPDATE concurrency locking.
     *
     * @param Property    $property
     * @param string      $checkIn   YYYY-MM-DD
     * @param string      $checkOut  YYYY-MM-DD
     * @param int|null    $roomTypeId
     * @param bool        $lockForUpdate
     * @return array
     */
    public function checkAvailability(
        Property $property,
        string $checkIn,
        string $checkOut,
        ?int $roomTypeId = null,
        bool $lockForUpdate = false
    ): array {
        $startDate = Carbon::parse($checkIn);
        $endDate   = Carbon::parse($checkOut);
        $nights    = $startDate->diffInDays($endDate);

        if ($nights <= 0) {
            return ['available' => false, 'reason' => 'Invalid date range.', 'room_types' => []];
        }

        $roomTypeQuery = RoomType::where('property_id', $property->id)->where('status', 'active');
        if ($roomTypeId) {
            $roomTypeQuery->where('id', $roomTypeId);
        }
        $roomTypes = $roomTypeQuery->get();

        $results = [];

        DB::transaction(function () use ($property, $startDate, $endDate, $nights, $roomTypes, $lockForUpdate, &$results) {

            foreach ($roomTypes as $rt) {
                $dailyAvailability = [];
                $minAvailable      = 999999;

                // Loop through each night
                for ($d = $startDate->copy(); $d->lt($endDate); $d->addDay()) {
                    $dateStr = $d->toDateString();

                    // Query inventory_days
                    $invQuery = InventoryDay::where('property_id', $property->id)
                        ->where('room_type_id', $rt->id)
                        ->where('date', $dateStr);

                    if ($lockForUpdate) {
                        $invQuery->lockForUpdate();
                    }

                    $invDay = $invQuery->first();

                    // Auto-initialize inventory day if it doesn't exist yet
                    if (!$invDay) {
                        $totalPhysicalRooms = Room::where('property_id', $property->id)
                            ->where('room_type_id', $rt->id)
                            ->whereNull('deleted_at')
                            ->count();

                        $invDay = InventoryDay::create([
                            'property_id'  => $property->id,
                            'room_type_id' => $rt->id,
                            'date'         => $dateStr,
                            'total'        => $totalPhysicalRooms,
                            'blocked'      => 0,
                            'sold'         => 0,
                            'holds'        => 0,
                            'protected'    => 0,
                        ]);
                    }

                    $availableForNight = $invDay->available;
                    $dailyAvailability[$dateStr] = [
                        'total'     => $invDay->total,
                        'blocked'   => $invDay->blocked,
                        'sold'      => $invDay->sold,
                        'holds'     => $invDay->holds,
                        'available' => $availableForNight,
                    ];

                    if ($availableForNight < $minAvailable) {
                        $minAvailable = $availableForNight;
                    }
                }

                $results[$rt->id] = [
                    'room_type_id'       => $rt->id,
                    'room_type_name'     => $rt->name,
                    'room_type_code'     => $rt->code,
                    'max_occupancy'      => $rt->max_occupancy,
                    'min_available'      => max(0, $minAvailable),
                    'is_available'       => $minAvailable > 0,
                    'daily_availability' => $dailyAvailability,
                ];
            }
        });

        return [
            'property_id' => $property->id,
            'check_in'    => $checkIn,
            'check_out'   => $checkOut,
            'nights'      => $nights,
            'room_types'  => $results,
        ];
    }
}
