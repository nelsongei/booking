<?php

namespace App\Http\Controllers\Guest;

use App\Domain\Folios\FolioLedgerService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\ChargeCode;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuestJourneyController extends Controller
{
    protected FolioLedgerService $ledgerService;

    public function __construct(FolioLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Mobile-first Accountless Pre-Arrival Registration Page.
     */
    public function preRegistration(string $token)
    {
        $reservation = Reservation::where('ulid', $token)
            ->orWhere('confirmation_number', $token)
            ->with(['primaryGuest', 'property', 'rooms.roomType'])
            ->firstOrFail();

        $lang = request('lang', 'en');

        return view('guest.pre-registration', compact('reservation', 'lang'));
    }

    /**
     * Store Pre-Arrival Data, Passport Upload, Terms Consent, Signature & Upsells.
     */
    public function storePreRegistration(Request $request, string $token)
    {
        $reservation = Reservation::where('ulid', $token)
            ->orWhere('confirmation_number', $token)
            ->with(['primaryGuest', 'property'])
            ->firstOrFail();

        $data = $request->validate([
            'expected_arrival_time' => 'required|string',
            'passport_number'       => 'required|string|max:50',
            'nationality'           => 'required|string|max:100',
            'dietary_preferences'   => 'nullable|string|max:255',
            'digital_signature'     => 'required|string', // Base64 data URL signature canvas
            'terms_consented'       => 'required|accepted',
            'passport_document'     => 'nullable|file|mimes:jpg,png,pdf|max:10240', // 10MB max
            'upsells'               => 'nullable|array',
        ]);

        // Secure ID Document Upload with Automatic Retention Rule (Expires 30 days post checkout)
        $idDocumentPath = null;
        if ($request->hasFile('passport_document')) {
            $idDocumentPath = $request->file('passport_document')->store('secure_identities/' . $reservation->property_id, 'local');
        }

        $retentionUntil = now()->addDays(30);

        DB::transaction(function () use ($reservation, $data, $idDocumentPath, $retentionUntil, $request) {
            // Update Primary Guest Profile with ID data
            DB::table('guest_profiles')
                ->where('id', $reservation->primary_guest_id)
                ->update([
                    'updated_at' => now(),
                ]);

            // Save Pre-Arrival Registration Metadata
            DB::table('digital_registration_cards')->updateOrInsert(
                ['reservation_id' => $reservation->id],
                [
                    'property_id'           => $reservation->property_id,
                    'passport_number'       => $data['passport_number'],
                    'nationality'           => $data['nationality'],
                    'expected_arrival_time' => $data['expected_arrival_time'],
                    'dietary_preferences'   => $data['dietary_preferences'] ?? null,
                    'digital_signature'     => $data['digital_signature'],
                    'terms_consented_at'    => now(),
                    'id_document_path'      => $idDocumentPath,
                    'id_retention_until'    => $retentionUntil,
                    'status'                => 'completed',
                    'updated_at'            => now(),
                    'created_at'            => now(),
                ]
            );

            // Post Selected Upsells to Folio Exactly Once (Idempotent)
            if (!empty($data['upsells'])) {
                $folio = $this->ledgerService->getOrCreateFolio($reservation);
                $chargeCode = ChargeCode::where('property_id', $reservation->property_id)->first();

                if (!$chargeCode) {
                    $chargeCode = ChargeCode::create([
                        'property_id'      => $reservation->property_id,
                        'code'             => 'UPSELL-01',
                        'name'             => 'Guest Pre-Arrival Upsell',
                        'category'         => 'other',
                        'revenue_category' => 'other',
                        'is_taxable'       => true,
                        'is_active'        => true,
                    ]);
                }

                foreach ($data['upsells'] as $upsellKey => $priceMinor) {
                    $upsellName = ucwords(str_replace('_', ' ', $upsellKey));
                    
                    // Check if upsell already posted to prevent duplicate charges
                    $alreadyPosted = DB::table('folio_transactions')
                        ->where('folio_account_id', $folio->id)
                        ->where('description', "Pre-Arrival Upsell: {$upsellName}")
                        ->exists();

                    if (!$alreadyPosted && (int)$priceMinor > 0) {
                        $this->ledgerService->postCharge(
                            $folio,
                            $chargeCode,
                            (int)$priceMinor,
                            "Pre-Arrival Upsell: {$upsellName}",
                            null
                        );
                    }
                }
            }

            $reservation->update([
                'special_requests' => ($reservation->special_requests ? $reservation->special_requests . "\n" : '')
                    . "[DIGITAL REGISTRATION COMPLETED] ETA: {$data['expected_arrival_time']} | Passport: {$data['passport_number']}",
            ]);
        });

        return redirect()->route('guest.pre-registration.success', ['token' => $reservation->ulid]);
    }

    public function preRegistrationSuccess(string $token)
    {
        $reservation = Reservation::where('ulid', $token)
            ->orWhere('confirmation_number', $token)
            ->with(['primaryGuest', 'property'])
            ->firstOrFail();

        return view('guest.success', compact('reservation'));
    }

    /**
     * Guest In-Stay Request Portal (Housekeeping & Maintenance).
     */
    public function requestPortal(string $token)
    {
        $reservation = Reservation::where('ulid', $token)
            ->orWhere('confirmation_number', $token)
            ->with(['primaryGuest', 'property', 'rooms.roomType'])
            ->firstOrFail();

        $requests = DB::table('guest_service_requests')
            ->where('reservation_id', $reservation->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('guest.request-portal', compact('reservation', 'requests'));
    }

    /**
     * Submit Guest Service Request.
     */
    public function storeRequest(Request $request, string $token)
    {
        $reservation = Reservation::where('ulid', $token)
            ->orWhere('confirmation_number', $token)
            ->firstOrFail();

        $data = $request->validate([
            'category' => 'required|in:housekeeping,maintenance,room_service,concierge',
            'details'  => 'required|string|max:500',
        ]);

        DB::table('guest_service_requests')->insert([
            'property_id'    => $reservation->property_id,
            'reservation_id' => $reservation->id,
            'category'       => $data['category'],
            'details'        => $data['details'],
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->back()->with('success', 'Your request has been sent to the front desk team!');
    }
}
