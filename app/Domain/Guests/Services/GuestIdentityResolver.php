<?php

namespace App\Domain\Guests\Services;

use App\Infrastructure\Persistence\GuestProfile;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Support\Facades\DB;

class GuestIdentityResolver
{
    /**
     * Detect potential duplicate profiles for a guest based on email or phone.
     */
    public function findDuplicates(GuestProfile $primary): array
    {
        $query = GuestProfile::where('organization_id', $primary->organization_id)
            ->where('id', '!=', $primary->id);

        $query->where(function ($q) use ($primary) {
            if (!empty($primary->email)) {
                $q->orWhere('email', $primary->email);
            }
            if (!empty($primary->phone)) {
                $q->orWhere('phone', $primary->phone);
            }
        });

        return $query->get()->toArray();
    }

    /**
     * Merge secondary guest profile into primary guest profile.
     */
    public function mergeProfiles(GuestProfile $primary, GuestProfile $secondary): void
    {
        DB::transaction(function () use ($primary, $secondary) {
            // Re-point all reservations to primary profile
            Reservation::where('primary_guest_id', $secondary->id)
                ->update(['primary_guest_id' => $primary->id]);

            // Soft-delete or mark secondary profile as merged
            $secondary->delete();
        });
    }
}
