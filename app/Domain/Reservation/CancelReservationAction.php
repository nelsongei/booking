<?php

namespace App\Domain\Reservation;

use App\Infrastructure\Persistence\InventoryDay;
use App\Infrastructure\Persistence\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CancelReservationAction
{
    protected ReservationStateMachine $stateMachine;

    public function __construct(ReservationStateMachine $stateMachine)
    {
        $this->stateMachine = $stateMachine;
    }

    public function execute(Reservation $reservation, string $reason): Reservation
    {
        if ($reservation->isCancelled()) {
            return $reservation;
        }

        return DB::transaction(function () use ($reservation, $reason) {
            // Decrement sold inventory count for each night of the reservation
            $startDate = Carbon::parse($reservation->check_in);
            $endDate   = Carbon::parse($reservation->check_out);

            foreach ($reservation->rooms as $room) {
                for ($d = $startDate->copy(); $d->lt($endDate); $d->addDay()) {
                    $invDay = InventoryDay::where('property_id', $reservation->property_id)
                        ->where('room_type_id', $room->room_type_id)
                        ->where('date', $d->toDateString())
                        ->lockForUpdate()
                        ->first();

                    if ($invDay && $invDay->sold > 0) {
                        $invDay->decrement('sold');
                    }
                }
            }

            // State transition to cancelled
            return $this->stateMachine->transition($reservation, 'cancelled', $reason);
        });
    }
}
