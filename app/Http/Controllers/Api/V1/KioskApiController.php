<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KioskApiController extends Controller
{
    /**
     * Accountless Self-Service Kiosk Check-In API.
     */
    public function kioskCheckin(Request $request)
    {
        $request->validate([
            'confirmation_number' => 'required|string',
            'passport_last4'      => 'nullable|string',
        ]);

        $reservation = Reservation::where('confirmation_number', $request->confirmation_number)
            ->with(['primaryGuest', 'rooms.roomType', 'property'])
            ->first();

        if (!$reservation) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Reservation not found. Please see front desk staff for assistance.',
            ], 404);
        }

        // Verify digital registration completion status
        $regCard = DB::table('digital_registration_cards')
            ->where('reservation_id', $reservation->id)
            ->first();

        if (!$regCard || $regCard->status !== 'completed') {
            return response()->json([
                'status'          => 'registration_required',
                'message'         => 'Pre-arrival registration is pending. Please complete registration card on kiosk screen.',
                'reservation_ulid'=> $reservation->ulid,
            ], 422);
        }

        // Execute Check-in
        $reservation->update(['status' => 'checked_in']);

        // Signal Key Card Encoder / BLE Mobile Key issuance
        $keyToken = 'KEY-' . Str::upper(Str::random(12));

        return response()->json([
            'status'         => 'success',
            'message'        => 'Check-in completed successfully! Enjoy your stay.',
            'confirmation'   => $reservation->confirmation_number,
            'guest_name'     => $reservation->primaryGuest->first_name . ' ' . $reservation->primaryGuest->last_name,
            'room_number'    => $reservation->rooms->first()->room_number ?? '101',
            'mobile_key_token' => $keyToken,
            'lock_provider'  => 'AssaAbloy_BLE',
        ]);
    }

    /**
     * Mobile Key Issuance API for Certified Lock Partners (Salto / Dormakaba / Assa Abloy).
     */
    public function issueMobileKey(Request $request)
    {
        $request->validate([
            'reservation_ulid' => 'required|string',
            'lock_partner'     => 'required|in:assa_abloy,salto,dormakaba',
        ]);

        $reservation = Reservation::where('ulid', $request->reservation_ulid)->firstOrFail();

        $bleToken = Str::upper($request->lock_partner) . '-BLE-' . Str::random(16);

        return response()->json([
            'status'        => 'issued',
            'lock_partner'  => $request->lock_partner,
            'room_number'   => $reservation->rooms->first()->room_number ?? '101',
            'valid_from'    => $reservation->check_in_date,
            'valid_until'   => $reservation->check_out_date,
            'digital_key'   => $bleToken,
        ]);
    }
}
