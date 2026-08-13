<?php

namespace App\Domain\Inventory;

use App\Infrastructure\Persistence\InventoryDay;
use App\Infrastructure\Persistence\InventoryHold;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HoldService
{
    /**
     * Create an inventory hold with FOR UPDATE row locking.
     */
    public function createHold(
        Property $property,
        RoomType $roomType,
        string $checkIn,
        string $checkOut,
        int $roomsCount = 1,
        int $ttlMinutes = 15,
        ?string $sessionToken = null,
        string $source = 'booking_engine'
    ): InventoryHold {
        $startDate = Carbon::parse($checkIn);
        $endDate   = Carbon::parse($checkOut);

        return DB::transaction(function () use ($property, $roomType, $checkIn, $checkOut, $startDate, $endDate, $roomsCount, $ttlMinutes, $sessionToken, $source) {
            // Lock and verify inventory for each night
            for ($d = $startDate->copy(); $d->lt($endDate); $d->addDay()) {
                $dateStr = $d->toDateString();

                $invDay = InventoryDay::where('property_id', $property->id)
                    ->where('room_type_id', $roomType->id)
                    ->where('date', $dateStr)
                    ->lockForUpdate()
                    ->first();

                if (!$invDay) {
                    $totalPhysicalRooms = Room::where('property_id', $property->id)
                        ->where('room_type_id', $roomType->id)
                        ->whereNull('deleted_at')
                        ->count();

                    $invDay = InventoryDay::create([
                        'property_id'  => $property->id,
                        'room_type_id' => $roomType->id,
                        'date'         => $dateStr,
                        'total'        => $totalPhysicalRooms,
                        'blocked'      => 0,
                        'sold'         => 0,
                        'holds'        => 0,
                        'protected'    => 0,
                    ]);
                }

                if ($invDay->available < $roomsCount) {
                    throw new \RuntimeException("Insufficient inventory available on {$dateStr} for room type '{$roomType->name}'.");
                }

                // Increment holds count on inventory day
                $invDay->increment('holds', $roomsCount);
            }

            return InventoryHold::create([
                'ulid'          => (string) Str::ulid(),
                'property_id'   => $property->id,
                'room_type_id'  => $roomType->id,
                'check_in'      => $checkIn,
                'check_out'     => $checkOut,
                'rooms_count'   => $roomsCount,
                'status'        => 'active',
                'source'        => $source,
                'session_token' => $sessionToken,
                'expires_at'    => now()->addMinutes($ttlMinutes),
            ]);
        });
    }

    /**
     * Release an active hold and decrement inventory holds count.
     */
    public function releaseHold(InventoryHold $hold): void
    {
        if ($hold->status !== 'active') {
            return;
        }

        $startDate = Carbon::parse($hold->check_in);
        $endDate   = Carbon::parse($hold->check_out);

        DB::transaction(function () use ($hold, $startDate, $endDate) {
            for ($d = $startDate->copy(); $d->lt($endDate); $d->addDay()) {
                $dateStr = $d->toDateString();

                $invDay = InventoryDay::where('property_id', $hold->property_id)
                    ->where('room_type_id', $hold->room_type_id)
                    ->where('date', $dateStr)
                    ->lockForUpdate()
                    ->first();

                if ($invDay && $invDay->holds >= $hold->rooms_count) {
                    $invDay->decrement('holds', $hold->rooms_count);
                }
            }

            $hold->update(['status' => 'released']);
        });
    }

    /**
     * Clean up all expired active holds.
     */
    public function releaseExpiredHolds(): int
    {
        $expiredHolds = InventoryHold::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expiredHolds as $hold) {
            $this->releaseHold($hold);
            $hold->update(['status' => 'expired']);
            $count++;
        }

        return $count;
    }
}
