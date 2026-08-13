<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\Stay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user     = auth()->user();
        $property = app()->bound('current.property') ? app('current.property') : null;

        $stats = [
            'arrivals_today'     => 0,
            'departures_today'   => 0,
            'in_house'           => 0,
            'reservations_total' => 0,
            'revenue_mtd'        => 0,
            'occupancy_today'    => 0,
            'rooms_total'        => 0,
            'rooms_occupied'     => 0,
            'rooms_reserved'     => 0,
            'rooms_available'    => 0,
            'rooms_not_ready'    => 0,
        ];

        $chartLabels        = [];
        $chartRevenueData   = [];
        $recentReservations = collect();

        if ($property) {
            $today = now($property->timezone)->toDateString();

            // 1. Arrivals Today
            $arrivalsToday = Reservation::where('property_id', $property->id)
                ->whereDate('check_in', $today)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->count();

            // 2. Departures Today
            $departuresToday = Reservation::where('property_id', $property->id)
                ->whereDate('check_out', $today)
                ->whereIn('status', ['checked_in', 'checked_out'])
                ->count();

            // 3. In-House Guests
            $inHouseCount = Stay::where('property_id', $property->id)
                ->where('status', 'checked_in')
                ->count();

            if ($inHouseCount === 0) {
                $inHouseCount = Reservation::where('property_id', $property->id)
                    ->where('status', 'checked_in')
                    ->count();
            }

            // 4. Active Bookings
            $activeBookings = Reservation::where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'checked_in', 'pending_payment'])
                ->count();

            // 5. MTD Revenue
            $resRevenueMtd = Reservation::where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_minor');

            $paymentRevenueMtd = DB::table('payments')
                ->where('property_id', $property->id)
                ->where('status', 'captured')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount_minor');

            $revenueMtd = max($resRevenueMtd, $paymentRevenueMtd);

            // 6. Physical Room Breakdown
            $totalRooms = Room::where('property_id', $property->id)
                ->whereNull('deleted_at')
                ->count();

            $occupiedRooms = Room::where('property_id', $property->id)
                ->whereNull('deleted_at')
                ->where('status', 'occupied')
                ->count();

            if ($occupiedRooms === 0 && $inHouseCount > 0) {
                $occupiedRooms = $inHouseCount;
            }

            $notReadyRooms = Room::where('property_id', $property->id)
                ->whereNull('deleted_at')
                ->whereIn('status', ['dirty', 'out_of_order', 'out_of_service'])
                ->count();

            $reservedRooms = Reservation::where('property_id', $property->id)
                ->whereDate('check_in', '<=', $today)
                ->whereDate('check_out', '>', $today)
                ->where('status', 'confirmed')
                ->count();

            $availableRooms = max(0, $totalRooms - $occupiedRooms - $notReadyRooms - $reservedRooms);

            $occupancyRate = $totalRooms > 0 
                ? round((($occupiedRooms + $reservedRooms) / $totalRooms) * 100, 1) 
                : 0;

            $stats = [
                'arrivals_today'     => $arrivalsToday,
                'departures_today'   => $departuresToday,
                'in_house'           => $inHouseCount,
                'reservations_total' => $activeBookings,
                'revenue_mtd'        => $revenueMtd,
                'occupancy_today'    => $occupancyRate,
                'rooms_total'        => $totalRooms,
                'rooms_occupied'     => $occupiedRooms,
                'rooms_reserved'     => $reservedRooms,
                'rooms_available'    => $availableRooms,
                'rooms_not_ready'    => $notReadyRooms,
            ];

            // 7. Executive Hospitality Metrics: RevPAR, ADR, Active Holds, Unpaid Balance
            $dayOfMonth = max(1, now()->day);
            $totalAvailableRoomNights = max(1, $totalRooms * $dayOfMonth);
            $revPar = round(($revenueMtd / 100) / $totalAvailableRoomNights, 2);
            $adr    = round(($revenueMtd / 100) / max(1, $activeBookings), 2);

            $activeHoldsCount = DB::table('inventory_holds')
                ->where('property_id', $property->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->count();

            $unpaidBalance = Reservation::where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->sum('balance_minor');

            $stats['revpar']         = $revPar;
            $stats['adr']            = $adr;
            $stats['active_holds']   = $activeHoldsCount;
            $stats['unpaid_balance'] = $unpaidBalance;

            // 8. Dynamic Monthly Revenue Analytics (Past 6 Months)
            for ($i = 5; $i >= 0; $i--) {
                $monthDate = now()->subMonths($i);
                $chartLabels[] = $monthDate->format('M Y');

                $mRev = Reservation::where('property_id', $property->id)
                    ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                    ->whereMonth('created_at', $monthDate->month)
                    ->whereYear('created_at', $monthDate->year)
                    ->sum('total_minor');

                $chartRevenueData[] = round($mRev / 100, 2);
            }

            // 9. Room Category Breakdown for Doughnut Chart
            $roomTypeStats = DB::table('reservation_rooms')
                ->join('reservations', 'reservation_rooms.reservation_id', '=', 'reservations.id')
                ->join('room_types', 'reservation_rooms.room_type_id', '=', 'room_types.id')
                ->where('reservations.property_id', $property->id)
                ->whereIn('reservations.status', ['confirmed', 'checked_in', 'checked_out'])
                ->select('room_types.name as room_type_name', DB::raw('count(reservation_rooms.id) as count'))
                ->groupBy('room_types.name')
                ->get();

            $roomTypeLabels = $roomTypeStats->pluck('room_type_name')->toArray();
            $roomTypeCounts = $roomTypeStats->pluck('count')->toArray();
            if (empty($roomTypeLabels)) {
                $roomTypeLabels = ['Deluxe King Room', 'Executive Suite'];
                $roomTypeCounts = [2, 1];
            }

            // 10. Booking Channel Breakdown Chart
            $channelStats = DB::table('reservations')
                ->where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->select('booking_channel', DB::raw('count(*) as count'))
                ->groupBy('booking_channel')
                ->get();

            $channelLabels = $channelStats->map(fn($c) => ucfirst(str_replace('_', ' ', $c->booking_channel)))->toArray();
            $channelCounts = $channelStats->pluck('count')->toArray();
            if (empty($channelLabels)) {
                $channelLabels = ['Direct Web', 'Staff / Front Desk', 'Booking.com OTA'];
                $channelCounts = [2, 1, 0];
            }

            // 11. Recent 5 Live Reservations
            $recentReservations = Reservation::where('property_id', $property->id)
                ->with(['primaryGuest', 'rooms.roomType'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // 12. Recent Live Stripe & Cash Payment Transactions
            $recentPayments = DB::table('payments')
                ->leftJoin('reservations', 'payments.reservation_id', '=', 'reservations.id')
                ->leftJoin('guest_profiles', 'reservations.primary_guest_id', '=', 'guest_profiles.id')
                ->where('payments.property_id', $property->id)
                ->select(
                    'payments.ulid',
                    'payments.provider',
                    'payments.provider_payment_id',
                    'payments.amount_minor',
                    'payments.currency',
                    'payments.status',
                    'payments.created_at',
                    'reservations.confirmation_number',
                    'guest_profiles.first_name',
                    'guest_profiles.last_name'
                )
                ->orderBy('payments.created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('admin.dashboard', compact(
            'user', 'property', 'stats', 'chartLabels', 'chartRevenueData', 
            'roomTypeLabels', 'roomTypeCounts', 'channelLabels', 'channelCounts', 
            'recentReservations', 'recentPayments'
        ));
    }

    public function switchProperty(Request $request)
    {
        $request->validate(['property_id' => 'required|integer']);

        $user     = auth()->user();
        $property = Property::findOrFail($request->property_id);

        abort_unless($user->canAccessProperty($property), 403, 'Access denied to this property.');

        session(['current_property_id' => $property->id]);

        return redirect()->back()->with('success', "Switched to {$property->name}");
    }
}
