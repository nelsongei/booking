<?php

namespace App\Domain\Scale;

use App\Infrastructure\Persistence\GuestProfile;
use App\Infrastructure\Persistence\LoyaltyAccount;
use App\Infrastructure\Persistence\LoyaltyTransaction;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoyaltyService
{
    /**
     * Get or create guest loyalty account.
     */
    public function getOrCreateLoyaltyAccount(GuestProfile $guest): LoyaltyAccount
    {
        return DB::transaction(function () use ($guest) {
            $account = LoyaltyAccount::where('guest_profile_id', $guest->id)->first();

            if (!$account) {
                $account = LoyaltyAccount::create([
                    'guest_profile_id' => $guest->id,
                    'account_number'   => 'LOY-' . strtoupper(Str::random(8)),
                    'tier'             => 'bronze',
                    'points_balance'   => 0,
                    'lifetime_points'  => 0,
                    'joined_at'        => now(),
                ]);
            }

            return $account;
        });
    }

    /**
     * Award loyalty points for a completed stay.
     */
    public function awardStayPoints(Reservation $reservation): ?LoyaltyTransaction
    {
        if (!$reservation->primaryGuest) {
            return null;
        }

        $account = $this->getOrCreateLoyaltyAccount($reservation->primaryGuest);
        
        // 1 point per $1 spent on subtotal
        $pointsEarned = (int) floor($reservation->subtotal_minor / 100);

        if ($pointsEarned <= 0) {
            return null;
        }

        return $this->adjustPoints(
            $account,
            $pointsEarned,
            'earn',
            "Earned from Stay #" . $reservation->confirmation_number,
            (string) $reservation->id
        );
    }

    /**
     * Adjust points balance and evaluate tier status.
     */
    public function adjustPoints(
        LoyaltyAccount $account,
        int $points,
        string $type = 'adjustment',
        string $description = 'Points adjustment',
        ?string $referenceId = null
    ): LoyaltyTransaction {
        return DB::transaction(function () use ($account, $points, $type, $description, $referenceId) {
            $transaction = LoyaltyTransaction::create([
                'loyalty_account_id' => $account->id,
                'type'               => $type,
                'points'             => $points,
                'description'        => $description,
                'reference_id'       => $referenceId,
            ]);

            $newBalance = max(0, $account->points_balance + $points);
            $newLifetime = $account->lifetime_points + ($points > 0 ? $points : 0);

            // Re-evaluate Tier
            $newTier = 'bronze';
            if ($newLifetime >= 15000) {
                $newTier = 'platinum';
            } elseif ($newLifetime >= 5000) {
                $newTier = 'gold';
            } elseif ($newLifetime >= 1000) {
                $newTier = 'silver';
            }

            $account->update([
                'points_balance'  => $newBalance,
                'lifetime_points' => $newLifetime,
                'tier'            => $newTier,
            ]);

            return $transaction;
        });
    }
}
