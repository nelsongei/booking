<?php

namespace App\Domain\Guests\Services;

use App\Infrastructure\Persistence\Reservation;
use Illuminate\Support\Facades\DB;

class DigitalRegistrationService
{
    /**
     * Submit pre-arrival digital registration card details.
     */
    public function submitRegistration(Reservation $reservation, array $details): bool
    {
        // $details includes: passport_number, nationality, expected_arrival, digital_signature
        DB::table('guest_profiles')
            ->where('id', $reservation->primary_guest_id)
            ->update([
                'phone' => $details['phone'] ?? null,
                'updated_at' => now(),
            ]);

        $reservation->update([
            'special_requests' => ($reservation->special_requests ? $reservation->special_requests . "\n" : '')
                . "Pre-Arrival Registration Completed. Estimated Arrival: " . ($details['expected_arrival'] ?? 'N/A'),
        ]);

        return true;
    }
}
