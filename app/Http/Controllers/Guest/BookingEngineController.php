<?php

namespace App\Http\Controllers\Guest;

use App\Domain\Inventory\AvailabilityService;
use App\Domain\Inventory\HoldService;
use App\Domain\Pricing\PricingEngine;
use App\Domain\Reservation\CreateReservationAction;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\InventoryHold;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingEngineController extends Controller
{
    protected AvailabilityService $availabilityService;
    protected PricingEngine $pricingEngine;
    protected HoldService $holdService;
    protected CreateReservationAction $createReservationAction;

    public function __construct(
        AvailabilityService $availabilityService,
        PricingEngine $pricingEngine,
        HoldService $holdService,
        CreateReservationAction $createReservationAction
    ) {
        $this->availabilityService       = $availabilityService;
        $this->pricingEngine              = $pricingEngine;
        $this->holdService                 = $holdService;
        $this->createReservationAction    = $createReservationAction;
    }

    /**
     * Resolve property by slug, booking_engine_slug, or code.
     */
    protected function resolveProperty(string $slug): Property
    {
        return Property::where('slug', $slug)
            ->orWhere('booking_engine_slug', $slug)
            ->orWhere('code', $slug)
            ->where('booking_engine_enabled', true)
            ->firstOrFail();
    }

    /**
     * Step 1: Property Booking Engine Landing Page.
     */
    public function index(Request $request, string $slug)
    {
        $property = $this->resolveProperty($slug);

        $checkIn  = $request->get('check_in', now()->addDay()->toDateString());
        $checkOut = $request->get('check_out', now()->addDays(3)->toDateString());
        $adults   = (int) $request->get('adults', 2);
        $children = (int) $request->get('children', 0);
        $rooms    = (int) $request->get('rooms', 1);

        return view('booking.index', compact('property', 'checkIn', 'checkOut', 'adults', 'children', 'rooms'));
    }

    /**
     * Step 2: Search Room Availability & Rate Quotes.
     */
    public function search(Request $request, string $slug)
    {
        $property = $this->resolveProperty($slug);

        $request->validate([
            'check_in'  => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults'    => 'integer|min:1|max:10',
            'children'  => 'integer|min:0|max:10',
            'rooms'     => 'integer|min:1|max:5',
        ]);

        $checkIn  = $request->input('check_in');
        $checkOut = $request->input('check_out');
        $adults   = (int) $request->input('adults', 2);
        $children = (int) $request->input('children', 0);
        $rooms    = (int) $request->input('rooms', 1);

        // Check availability for all active room types
        $availResult = $this->availabilityService->checkAvailability($property, $checkIn, $checkOut);

        $availableRoomTypes = [];
        $roomTypes = RoomType::where('property_id', $property->id)
            ->where('status', 'active')
            ->get();

        foreach ($roomTypes as $rt) {
            $rtAvail = $availResult['room_types'][$rt->id] ?? null;
            if (!$rtAvail || !$rtAvail['is_available'] || $rtAvail['min_available'] < 1) {
                continue;
            }

            // Get rate plans for this room type
            $ratePlans = RatePlan::where('property_id', $property->id)
                ->where('is_active', true)
                ->get();

            $quotedPlans = [];
            foreach ($ratePlans as $rp) {
                try {
                    $quote = $this->pricingEngine->calculate($property, $rt, $rp, $checkIn, $checkOut, $adults, $children);
                    
                    // Multiply quote total by rooms quantity if > 1
                    if ($rooms > 1) {
                        $quote['rooms_count'] = $rooms;
                        $quote['total_minor_single'] = $quote['total_minor'];
                        $quote['total_minor'] = $quote['total_minor'] * $rooms;
                        $quote['tax_total_minor'] = $quote['tax_total_minor'] * $rooms;
                    } else {
                        $quote['rooms_count'] = 1;
                        $quote['total_minor_single'] = $quote['total_minor'];
                    }

                    $quotedPlans[] = [
                        'rate_plan' => $rp,
                        'quote'     => $quote,
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!empty($quotedPlans)) {
                $availableRoomTypes[] = [
                    'room_type'      => $rt,
                    'min_available'  => $rtAvail['min_available'],
                    'rate_plans'     => $quotedPlans,
                ];
            }
        }

        return view('booking.results', compact(
            'property', 'checkIn', 'checkOut', 'adults', 'children', 'rooms', 'availableRoomTypes'
        ));
    }

    /**
     * Step 3: Add-ons & Extras Selection.
     */
    public function addons(Request $request, string $slug)
    {
        $property = $this->resolveProperty($slug);

        if ($request->isMethod('get') || !$request->has('room_type_id')) {
            return redirect()->route('booking.search', ['slug' => $slug])
                ->with('error', 'Your booking session has expired. Please select your travel dates and room to continue.');
        }

        $request->validate([
            'check_in'     => 'required|date',
            'check_out'    => 'required|date',
            'room_type_id' => 'required|exists:room_types,id',
            'rate_plan_id' => 'required|exists:rate_plans,id',
            'adults'       => 'required|integer|min:1',
            'children'     => 'required|integer|min:0',
            'rooms'        => 'integer|min:1|max:5',
        ]);

        $roomType = RoomType::findOrFail($request->input('room_type_id'));
        $ratePlan = RatePlan::findOrFail($request->input('rate_plan_id'));
        $checkIn  = $request->input('check_in');
        $checkOut = $request->input('check_out');
        $adults   = (int) $request->input('adults', 2);
        $children = (int) $request->input('children', 0);
        $rooms    = (int) $request->input('rooms', 1);

        $pricing  = $this->pricingEngine->calculate($property, $roomType, $ratePlan, $checkIn, $checkOut, $adults, $children);

        if ($rooms > 1) {
            $pricing['rooms_count'] = $rooms;
            $pricing['total_minor_single'] = $pricing['total_minor'];
            $pricing['total_minor'] = $pricing['total_minor'] * $rooms;
            $pricing['tax_total_minor'] = $pricing['tax_total_minor'] * $rooms;
        }

        return view('booking.addons', compact(
            'property', 'roomType', 'ratePlan', 'checkIn', 'checkOut', 'adults', 'children', 'rooms', 'pricing'
        ));
    }

    /**
     * Step 4: Guest Contact Details Form.
     */
    public function guestDetails(Request $request, string $slug)
    {
        $property = $this->resolveProperty($slug);

        if ($request->isMethod('get') || !$request->has('room_type_id')) {
            return redirect()->route('booking.search', ['slug' => $slug])
                ->with('error', 'Your booking session has expired. Please select your travel dates and room to continue.');
        }

        $request->validate([
            'check_in'     => 'required|date',
            'check_out'    => 'required|date',
            'room_type_id' => 'required|exists:room_types,id',
            'rate_plan_id' => 'required|exists:rate_plans,id',
            'adults'       => 'required|integer|min:1',
            'children'     => 'required|integer|min:0',
            'rooms'        => 'integer|min:1|max:5',
        ]);

        $roomType = RoomType::findOrFail($request->input('room_type_id'));
        $ratePlan = RatePlan::findOrFail($request->input('rate_plan_id'));
        $checkIn  = $request->input('check_in');
        $checkOut = $request->input('check_out');
        $adults   = (int) $request->input('adults', 2);
        $children = (int) $request->input('children', 0);
        $rooms    = (int) $request->input('rooms', 1);
        $addons   = $request->input('addons', []);

        $pricing  = $this->pricingEngine->calculate($property, $roomType, $ratePlan, $checkIn, $checkOut, $adults, $children);

        if ($rooms > 1) {
            $pricing['rooms_count'] = $rooms;
            $pricing['total_minor_single'] = $pricing['total_minor'];
            $pricing['total_minor'] = $pricing['total_minor'] * $rooms;
            $pricing['tax_total_minor'] = $pricing['tax_total_minor'] * $rooms;
        }

        return view('booking.guest_details', compact(
            'property', 'roomType', 'ratePlan', 'checkIn', 'checkOut', 'adults', 'children', 'rooms', 'addons', 'pricing'
        ));
    }

    /**
     * Step 5: Review & 15-Minute Transient Inventory Hold.
     */
    public function reviewAndHold(Request $request, string $slug)
    {
        $property = $this->resolveProperty($slug);

        if ($request->isMethod('get') || !$request->has('room_type_id') || !$request->has('guest_email')) {
            return redirect()->route('booking.search', ['slug' => $slug])
                ->with('error', 'Your 15-minute room hold or booking session has expired. Please select your dates and room to continue.');
        }

        $request->validate([
            'check_in'         => 'required|date',
            'check_out'        => 'required|date',
            'room_type_id'     => 'required|exists:room_types,id',
            'rate_plan_id'     => 'required|exists:rate_plans,id',
            'adults'           => 'required|integer|min:1',
            'children'         => 'required|integer|min:0',
            'rooms'            => 'integer|min:1|max:5',
            'guest_first_name' => 'required|string|max:100',
            'guest_last_name'  => 'required|string|max:100',
            'guest_email'      => 'required|email|max:150',
            'guest_phone'      => 'required|string|max:30',
        ]);

        $roomType = RoomType::findOrFail($request->input('room_type_id'));
        $ratePlan = RatePlan::findOrFail($request->input('rate_plan_id'));
        $checkIn  = $request->input('check_in');
        $checkOut = $request->input('check_out');
        $adults   = (int) $request->input('adults', 2);
        $children = (int) $request->input('children', 0);
        $rooms    = (int) $request->input('rooms', 1);

        // Place a 15-minute transient row-locked inventory hold for $rooms quantity
        $sessionToken = session()->getId();
        try {
            $hold = $this->holdService->createHold(
                $property,
                $roomType,
                $checkIn,
                $checkOut,
                $rooms,
                15,
                $sessionToken,
                'direct_web'
            );
        } catch (\Exception $e) {
            return redirect()->route('booking.search', ['slug' => $slug])
                ->with('error', 'Sorry, the selected room is no longer available for your dates: ' . $e->getMessage());
        }

        $pricing = $this->pricingEngine->calculate($property, $roomType, $ratePlan, $checkIn, $checkOut, $adults, $children);

        if ($rooms > 1) {
            $pricing['rooms_count'] = $rooms;
            $pricing['total_minor_single'] = $pricing['total_minor'];
            $pricing['total_minor'] = $pricing['total_minor'] * $rooms;
            $pricing['tax_total_minor'] = $pricing['tax_total_minor'] * $rooms;
        }

        $guestData = [
            'first_name'       => $request->input('guest_first_name'),
            'last_name'        => $request->input('guest_last_name'),
            'email'            => $request->input('guest_email'),
            'phone'            => $request->input('guest_phone'),
            'special_requests' => $request->input('special_requests'),
        ];

        return view('booking.review', compact(
            'property', 'roomType', 'ratePlan', 'checkIn', 'checkOut', 'adults', 'children', 'rooms',
            'hold', 'pricing', 'guestData'
        ));
    }

    /**
     * Step 6: Complete Booking Execution.
     */
    public function confirm(Request $request, string $slug)
    {
        $property = $this->resolveProperty($slug);

        if ($request->isMethod('get') || !$request->has('room_type_id') || !$request->has('guest_email')) {
            return redirect()->route('booking.search', ['slug' => $slug])
                ->with('error', 'Your 15-minute room hold or booking session has expired. Please select your dates and room to continue.');
        }

        $request->validate([
            'check_in'         => 'required|date',
            'check_out'        => 'required|date',
            'room_type_id'     => 'required|exists:room_types,id',
            'rate_plan_id'     => 'required|exists:rate_plans,id',
            'adults'           => 'required|integer|min:1',
            'children'         => 'required|integer|min:0',
            'rooms'            => 'integer|min:1|max:5',
            'guest_first_name' => 'required|string|max:100',
            'guest_last_name'  => 'required|string|max:100',
            'guest_email'      => 'required|email|max:150',
            'guest_phone'      => 'required|string|max:30',
            'hold_ulid'        => 'nullable|string',
        ]);

        try {
            $data = [
                'property_id'       => $property->id,
                'room_type_id'      => $request->input('room_type_id'),
                'rate_plan_id'      => $request->input('rate_plan_id'),
                'check_in'          => $request->input('check_in'),
                'check_out'         => $request->input('check_out'),
                'adults'            => (int) $request->input('adults', 2),
                'children'          => (int) $request->input('children', 0),
                'guest_first_name'  => $request->input('guest_first_name'),
                'guest_last_name'   => $request->input('guest_last_name'),
                'guest_email'       => $request->input('guest_email'),
                'guest_phone'       => $request->input('guest_phone'),
                'special_requests'  => $request->input('special_requests'),
                'booking_channel'   => 'direct_web',
                'hold_ulid'         => $request->input('hold_ulid'),
                'payment_method'    => $request->input('payment_method', 'stripe'),
                'stripe_payment_id' => $request->input('stripe_payment_id'),
                'card_last4'        => $request->input('card_last4', '4242'),
                'card_brand'        => $request->input('card_brand', 'visa'),
            ];

            $reservation = $this->createReservationAction->execute($data);

            return redirect()->route('booking.confirmation', [
                'slug'                => $property->slug,
                'confirmationNumber' => $reservation->confirmation_number,
            ])->with('success', 'Your reservation has been confirmed!');

        } catch (\Exception $e) {
            return redirect()->route('booking.search', ['slug' => $property->slug])
                ->with('error', 'Booking processing error: ' . $e->getMessage());
        }
    }

    /**
     * Confirmation Screen.
     */
    public function confirmation(string $slug, string $confirmationNumber)
    {
        $property    = $this->resolveProperty($slug);
        $reservation = Reservation::where('property_id', $property->id)
            ->where('confirmation_number', $confirmationNumber)
            ->firstOrFail();

        return view('booking.confirmation', compact('property', 'reservation'));
    }
}
