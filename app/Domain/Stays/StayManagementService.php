<?php

namespace App\Domain\Stays;

use App\Domain\Reservation\CancelReservationAction;
use App\Domain\Reservation\ReservationStateMachine;
use App\Infrastructure\Persistence\CheckinRecord;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\ReservationRoom;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\RoomAssignment;
use App\Infrastructure\Persistence\Stay;
use App\Infrastructure\Persistence\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StayManagementService
{
    protected ReservationStateMachine $stateMachine;
    protected CancelReservationAction $cancelReservationAction;

    public function __construct(
        ReservationStateMachine $stateMachine,
        CancelReservationAction $cancelReservationAction
    ) {
        $this->stateMachine            = $stateMachine;
        $this->cancelReservationAction = $cancelReservationAction;
    }

    /**
     * Execute Check-In workflow for a reservation and assign a physical room.
     */
    public function executeCheckIn(
        Reservation $reservation,
        Room $room,
        array $checkinData = [],
        ?User $staff = null
    ): Stay {
        return DB::transaction(function () use ($reservation, $room, $checkinData, $staff) {
            $staffId = $staff?->id ?: auth()->id();
            $resRoom = $reservation->rooms()->first();

            if (!$resRoom) {
                $resRoom = ReservationRoom::create([
                    'ulid'           => (string) Str::ulid(),
                    'reservation_id' => $reservation->id,
                    'room_type_id'  => $room->room_type_id,
                    'adults'         => $reservation->adults ?? 1,
                    'children'       => $reservation->children ?? 0,
                    'status'         => 'confirmed',
                    'subtotal_minor' => 0,
                    'tax_minor'      => 0,
                    'total_minor'    => 0,
                ]);
            }

            // 1. Create Stay record
            $stay = Stay::create([
                'ulid'                => (string) Str::ulid(),
                'reservation_id'      => $reservation->id,
                'reservation_room_id' => $resRoom->id,
                'property_id'         => $reservation->property_id,
                'room_id'             => $room->id,
                'status'              => 'checked_in',
                'arrival_date'        => $reservation->check_in,
                'departure_date'      => $reservation->check_out,
                'checked_in_at'       => now(),
                'checked_in_by'       => $staffId,
            ]);

            // 2. Create Room Assignment
            RoomAssignment::create([
                'stay_id'     => $stay->id,
                'room_id'     => $room->id,
                'property_id' => $reservation->property_id,
                'start_date'  => $reservation->check_in,
                'end_date'    => $reservation->check_out,
                'is_active'   => true,
            ]);

            // 3. Create Check-in verification record
            CheckinRecord::create([
                'stay_id'              => $stay->id,
                'id_type'              => $checkinData['id_type'] ?? 'passport',
                'id_number'            => $checkinData['id_number'] ?? null,
                'id_country'           => $checkinData['id_country'] ?? null,
                'id_expiry'            => $checkinData['id_expiry'] ?? null,
                'guest_signature_path' => $checkinData['guest_signature_path'] ?? null,
                'additional_guests'    => $checkinData['additional_guests'] ?? [],
                'notes'                => $checkinData['notes'] ?? null,
            ]);

            // 4. Update physical room status to occupied
            $room->update(['status' => 'occupied']);

            // 5. Transition Reservation state
            $this->stateMachine->transition($reservation, 'checked_in', 'Guest checked in at front desk', $staffId);

            return $stay;
        });
    }

    /**
     * Execute Check-Out workflow.
     */
    public function executeCheckOut(Stay $stay, ?User $staff = null): void
    {
        DB::transaction(function () use ($stay, $staff) {
            $staffId     = $staff?->id ?: auth()->id();
            $reservation = $stay->reservation;

            // 1. Update stay status
            $stay->update([
                'status'         => 'checked_out',
                'checked_out_at' => now(),
                'checked_out_by' => $staffId,
            ]);

            // 2. Deactivate room assignment
            RoomAssignment::where('stay_id', $stay->id)->update(['is_active' => false]);

            // 3. Automatically update physical room housekeeping status to dirty
            if ($stay->room) {
                $stay->room->update(['status' => 'dirty']);
            }

            // 4. Transition Reservation state to checked_out
            if ($reservation && $reservation->status === 'checked_in') {
                $this->stateMachine->transition($reservation, 'checked_out', 'Guest checked out at front desk', $staffId);
            }
        });
    }

    /**
     * Execute Room Move mid-stay.
     */
    public function executeRoomMove(Stay $stay, Room $newRoom, string $reason = 'Guest request', ?User $staff = null): void
    {
        DB::transaction(function () use ($stay, $newRoom, $reason) {
            $oldRoom = $stay->room;

            // Deactivate previous assignment
            RoomAssignment::where('stay_id', $stay->id)->where('is_active', true)->update([
                'end_date'  => now()->toDateString(),
                'is_active' => false,
            ]);

            // Create new assignment
            RoomAssignment::create([
                'stay_id'     => $stay->id,
                'room_id'     => $newRoom->id,
                'property_id' => $stay->property_id,
                'start_date'  => now()->toDateString(),
                'end_date'    => $stay->departure_date,
                'is_active'   => true,
            ]);

            // Update stay room
            $stay->update(['room_id' => $newRoom->id]);

            // Update room housekeeping states
            if ($oldRoom) {
                $oldRoom->update(['status' => 'dirty']);
            }
            $newRoom->update(['status' => 'occupied']);
        });
    }

    /**
     * Process No-Show for expected arrival.
     */
    public function executeNoShow(Reservation $reservation, ?User $staff = null): void
    {
        $staffId = $staff?->id ?: auth()->id();
        $this->stateMachine->transition($reservation, 'no_show', 'Marked as No-Show', $staffId);
    }
}
