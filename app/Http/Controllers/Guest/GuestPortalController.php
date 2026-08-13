<?php

namespace App\Http\Controllers\Guest;

use App\Domain\Reservation\CancelReservationAction;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Http\Request;

class GuestPortalController extends Controller
{
    protected CancelReservationAction $cancelReservationAction;

    public function __construct(CancelReservationAction $cancelReservationAction)
    {
        $this->cancelReservationAction = $cancelReservationAction;
    }

    /**
     * Show guest portal lookup form.
     */
    public function lookupForm()
    {
        $property = \App\Infrastructure\Persistence\Property::where('booking_engine_enabled', true)->first();
        return view('booking.portal.lookup', compact('property'));
    }

    /**
     * Search reservation by confirmation number + guest email.
     */
    public function searchReservation(Request $request)
    {
        $request->validate([
            'confirmation_number' => 'required|string',
            'email'               => 'required|email',
        ]);

        $confNum = strtoupper(trim($request->input('confirmation_number')));
        $email   = strtolower(trim($request->input('email')));

        $reservation = Reservation::where('confirmation_number', $confNum)
            ->whereHas('primaryGuest', function ($q) use ($email) {
                $q->where('email', $email);
            })
            ->first();

        if (!$reservation) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No matching reservation found for the provided confirmation number and email address.');
        }

        return redirect()->route('booking.portal.show', [
            'confirmationNumber' => $reservation->confirmation_number,
        ])->with('email', $email);
    }

    /**
     * Display reservation details in portal.
     */
    public function showReservation(Request $request, string $confirmationNumber)
    {
        $reservation = Reservation::where('confirmation_number', strtoupper($confirmationNumber))->firstOrFail();
        $property    = $reservation->property ?? \App\Infrastructure\Persistence\Property::first();

        return view('booking.portal.show', compact('reservation', 'property'));
    }

    /**
     * Guest self-service cancellation request.
     */
    public function cancelReservation(Request $request, string $confirmationNumber)
    {
        $reservation = Reservation::where('confirmation_number', strtoupper($confirmationNumber))->firstOrFail();

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if (in_array($reservation->status, ['checked_in', 'checked_out', 'cancelled'])) {
            return redirect()->back()->with('error', "Reservation cannot be cancelled in its current state ('{$reservation->status}').");
        }

        try {
            $this->cancelReservationAction->execute(
                $reservation,
                $request->input('reason', 'Cancelled by guest via self-service portal.')
            );

            return redirect()->route('booking.portal.show', ['confirmationNumber' => $reservation->confirmation_number])
                ->with('success', 'Your reservation has been cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cancellation failed: ' . $e->getMessage());
        }
    }
}
