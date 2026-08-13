<?php

namespace App\Domain\Reservation;

use App\Domain\Audit\AuditService;
use App\Domain\Inventory\AvailabilityService;
use App\Domain\Pricing\PricingEngine;
use App\Domain\Inventory\HoldService;
use App\Infrastructure\Persistence\GuestProfile;
use App\Infrastructure\Persistence\InventoryDay;
use App\Infrastructure\Persistence\InventoryHold;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\ReservationNight;
use App\Infrastructure\Persistence\ReservationRoom;
use App\Infrastructure\Persistence\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateReservationAction
{
    protected AvailabilityService $availabilityService;
    protected PricingEngine $pricingEngine;
    protected HoldService $holdService;

    public function __construct(
        AvailabilityService $availabilityService,
        PricingEngine $pricingEngine,
        HoldService $holdService
    ) {
        $this->availabilityService = $availabilityService;
        $this->pricingEngine       = $pricingEngine;
        $this->holdService          = $holdService;
    }

    public function execute(array $data): Reservation
    {
        $property = Property::findOrFail($data['property_id']);
        $roomType = RoomType::findOrFail($data['room_type_id']);
        $ratePlan = RatePlan::findOrFail($data['rate_plan_id']);

        $checkIn  = $data['check_in'];
        $checkOut = $data['check_out'];
        $adults   = (int) ($data['adults'] ?? 2);
        $children = (int) ($data['children'] ?? 0);

        return DB::transaction(function () use ($property, $roomType, $ratePlan, $checkIn, $checkOut, $adults, $children, $data) {

            // 0. Release hold if provided (converting hold to reservation)
            if (!empty($data['hold_id']) || !empty($data['hold_ulid'])) {
                $holdQuery = InventoryHold::where('property_id', $property->id)->where('status', 'active');
                if (!empty($data['hold_ulid'])) {
                    $holdQuery->where('ulid', $data['hold_ulid']);
                } else {
                    $holdQuery->where('id', $data['hold_id']);
                }
                $hold = $holdQuery->first();
                if ($hold) {
                    $this->holdService->releaseHold($hold);
                    $hold->update(['status' => 'converted']);
                }
            }

            // 1. Lock and verify availability
            $availability = $this->availabilityService->checkAvailability($property, $checkIn, $checkOut, $roomType->id, true);
            $rtAvail = $availability['room_types'][$roomType->id] ?? null;

            if (!$rtAvail || !$rtAvail['is_available']) {
                throw new \RuntimeException("Room type '{$roomType->name}' is not available for the requested dates.");
            }

            // 2. Compute pricing
            $pricing = $this->pricingEngine->calculate($property, $roomType, $ratePlan, $checkIn, $checkOut, $adults, $children);

            // 3. Find or create guest profile
            $guest = null;
            if (!empty($data['guest_email'])) {
                $guest = GuestProfile::firstOrCreate(
                    ['organization_id' => $property->organization_id, 'email' => $data['guest_email']],
                    [
                        'ulid'       => (string) Str::ulid(),
                        'first_name' => $data['guest_first_name'] ?? 'Guest',
                        'last_name'  => $data['guest_last_name'] ?? 'Customer',
                        'phone'      => $data['guest_phone'] ?? null,
                    ]
                );
            }

            // 4. Increment sold count in inventory_days
            $startDate = Carbon::parse($checkIn);
            $endDate   = Carbon::parse($checkOut);

            for ($d = $startDate->copy(); $d->lt($endDate); $d->addDay()) {
                $invDay = InventoryDay::where('property_id', $property->id)
                    ->where('room_type_id', $roomType->id)
                    ->where('date', $d->toDateString())
                    ->lockForUpdate()
                    ->first();

                if ($invDay) {
                    $invDay->increment('sold');
                }
            }

            // 5. Create Master Reservation
            $reservation = Reservation::create([
                'ulid'                => (string) Str::ulid(),
                'confirmation_number' => ConfirmationNumberGenerator::generate($property),
                'organization_id'     => $property->organization_id,
                'property_id'         => $property->id,
                'primary_guest_id'    => $guest?->id,
                'rate_plan_id'        => $ratePlan->id,
                'status'              => 'confirmed',
                'check_in'            => $checkIn,
                'check_out'           => $checkOut,
                'nights'              => $pricing['nights'],
                'rooms_count'         => 1,
                'adults'              => $adults,
                'children'            => $children,
                'currency'            => $pricing['currency'],
                'subtotal_minor'      => $pricing['subtotal_minor'],
                'tax_minor'           => $pricing['tax_total_minor'],
                'fee_minor'           => 0,
                'discount_minor'      => 0,
                'total_minor'         => $pricing['total_minor'],
                'deposit_minor'       => 0,
                'balance_minor'       => $pricing['total_minor'],
                'special_requests'    => $data['special_requests'] ?? null,
                'booking_channel'     => $data['booking_channel'] ?? 'staff',
                'created_by'          => auth()->id(),
                'confirmed_at'        => now(),
            ]);

            // 6. Create Reservation Room
            $resRoom = ReservationRoom::create([
                'ulid'           => (string) Str::ulid(),
                'reservation_id' => $reservation->id,
                'room_type_id'   => $roomType->id,
                'rate_plan_id'   => $ratePlan->id,
                'adults'         => $adults,
                'children'       => $children,
                'status'         => 'active',
                'subtotal_minor' => $pricing['subtotal_minor'],
                'tax_minor'      => $pricing['tax_total_minor'],
                'total_minor'    => $pricing['total_minor'],
                'rate_snapshot'  => $pricing['nightly'],
            ]);

            // 7. Create Reservation Nights
            foreach ($pricing['nightly'] as $night) {
                ReservationNight::create([
                    'reservation_room_id' => $resRoom->id,
                    'date'                => $night['date'],
                    'rate_minor'          => $night['night_total_minor'],
                    'tax_minor'           => 0,
                    'total_minor'         => $night['night_total_minor'],
                    'currency'            => $pricing['currency'],
                    'breakdown'           => $night,
                ]);
            }

            // 8. Record Stripe Payment if selected
            if (!empty($data['payment_method']) && $data['payment_method'] === 'stripe') {
                $stripePaymentId = $data['stripe_payment_id'] ?? ('ch_stripe_' . Str::lower(Str::random(16)));
                $cardLast4       = $data['card_last4'] ?? '4242';
                $cardBrand       = $data['card_brand'] ?? 'visa';

                \App\Infrastructure\Persistence\Payment::create([
                    'ulid'                    => (string) Str::ulid(),
                    'reservation_id'          => $reservation->id,
                    'property_id'             => $property->id,
                    'provider'                => 'stripe',
                    'provider_payment_id'     => $stripePaymentId,
                    'provider_payment_method' => 'card_' . $cardBrand,
                    'amount_minor'            => $pricing['total_minor'],
                    'currency'                => $pricing['currency'],
                    'status'                  => 'captured',
                    'type'                    => 'capture',
                    'captured_at'             => now(),
                    'provider_metadata'       => [
                        'card_last4' => $cardLast4,
                        'card_brand' => $cardBrand,
                        'paid_via'   => 'Stripe Online Checkout',
                    ],
                ]);

                // Update reservation deposit and balance
                $reservation->update([
                    'deposit_minor' => $pricing['total_minor'],
                    'balance_minor' => 0,
                ]);
            }

            AuditService::log('reservation.created', 'Reservation', $reservation->ulid, null, $reservation->toArray(), [
                'property_id' => $property->id,
            ]);

            return $reservation;
        });
    }
}
