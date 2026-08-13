<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Scale\LoyaltyService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\GuestProfile;
use App\Infrastructure\Persistence\LoyaltyAccount;
use App\Infrastructure\Persistence\Property;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    protected LoyaltyService $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->middleware('auth');
        $this->loyaltyService = $loyaltyService;
    }

    protected function resolveCurrentProperty(): ?Property
    {
        return app()->bound('current.property') ? app('current.property') : Property::first();
    }

    /**
     * Loyalty Program Roster & Ledger View.
     */
    public function index(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $loyaltyAccounts = LoyaltyAccount::with(['guestProfile', 'transactions'])->latest()->get();
        $guestsWithoutAccount = GuestProfile::whereDoesntHave('loyaltyAccount')->get();

        return view('admin.modules.loyalty', compact('property', 'loyaltyAccounts', 'guestsWithoutAccount'));
    }

    /**
     * Enroll guest into Loyalty Program.
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'guest_profile_id' => 'required|exists:guest_profiles,id',
        ]);

        $guest = GuestProfile::findOrFail($request->input('guest_profile_id'));

        try {
            $account = $this->loyaltyService->getOrCreateLoyaltyAccount($guest);
            return redirect()->back()->with('success', "Guest {$guest->fullName} enrolled in Loyalty Program. Account: {$account->account_number}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Enrollment failed: ' . $e->getMessage());
        }
    }

    /**
     * Manual Points Adjustment.
     */
    public function adjustPoints(Request $request, LoyaltyAccount $account)
    {
        $request->validate([
            'points'      => 'required|integer',
            'type'        => 'required|string|in:earn,redeem,adjustment',
            'description' => 'required|string|max:255',
        ]);

        $points = (int) $request->input('points');
        if ($request->input('type') === 'redeem' && $points > 0) {
            $points = -$points;
        }

        try {
            $this->loyaltyService->adjustPoints(
                $account,
                $points,
                $request->input('type'),
                $request->input('description')
            );

            return redirect()->back()->with('success', 'Loyalty points adjusted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Points adjustment failed: ' . $e->getMessage());
        }
    }
}
