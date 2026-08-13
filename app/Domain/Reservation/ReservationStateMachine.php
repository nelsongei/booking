<?php

namespace App\Domain\Reservation;

use App\Domain\Audit\AuditService;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\ReservationStatusHistory;
use InvalidArgumentException;

class ReservationStateMachine
{
    /**
     * Map of allowed state transitions.
     */
    protected static array $transitions = [
        'inquiry'         => ['held', 'confirmed', 'cancelled'],
        'held'            => ['pending_payment', 'confirmed', 'cancelled', 'expired'],
        'pending_payment' => ['confirmed', 'cancelled', 'expired'],
        'confirmed'       => ['checked_in', 'cancelled', 'no_show'],
        'checked_in'      => ['checked_out'],
        'checked_out'     => [], // terminal state
        'cancelled'       => [], // terminal state
        'no_show'         => [], // terminal state
    ];

    /**
     * Check if a transition is valid.
     */
    public static function canTransition(string $fromStatus, string $toStatus): bool
    {
        $allowed = self::$transitions[$fromStatus] ?? [];
        return in_array($toStatus, $allowed, true);
    }

    /**
     * Transition a reservation to a new state.
     */
    public function transition(Reservation $reservation, string $toStatus, ?string $reason = null, ?int $userId = null): Reservation
    {
        $fromStatus = $reservation->status;

        if ($fromStatus === $toStatus) {
            return $reservation;
        }

        if (!self::canTransition($fromStatus, $toStatus)) {
            throw new InvalidArgumentException("Cannot transition reservation from '{$fromStatus}' to '{$toStatus}'.");
        }

        $reservation->status = $toStatus;

        if ($toStatus === 'confirmed') {
            $reservation->confirmed_at = now();
        } elseif ($toStatus === 'cancelled') {
            $reservation->cancelled_at = now();
            $reservation->cancellation_reason = $reason;
        }

        $reservation->save();

        // Record history
        ReservationStatusHistory::create([
            'reservation_id' => $reservation->id,
            'from_status'    => $fromStatus,
            'to_status'      => $toStatus,
            'reason'         => $reason,
            'changed_by'     => $userId ?: auth()->id(),
            'changed_at'     => now(),
        ]);

        AuditService::log("reservation.status_changed.{$toStatus}", 'Reservation', $reservation->ulid, [
            'status' => $fromStatus,
        ], [
            'status' => $toStatus,
            'reason' => $reason,
        ], [
            'property_id' => $reservation->property_id,
        ]);

        return $reservation;
    }
}
