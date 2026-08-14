<?php

namespace App\Domain\Reporting;

use App\Infrastructure\Persistence\BookingSource;
use App\Infrastructure\Persistence\FolioTransaction;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\Stay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class KPIAnalyticsService
{
    /**
     * Compute aggregated KPI summary metrics for a given date range.
     */
    public function getMetricsOverview(Property $property, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();
        $daysCount = max(1, $start->diffInDays($end) + 1);

        $totalRooms = Room::where('property_id', $property->id)->whereNull('deleted_at')->count();
        if ($totalRooms === 0) {
            $totalRooms = 1;
        }
        $totalAvailableRoomNights = $totalRooms * $daysCount;

        // Query active reservations within date range OR created in date range
        $reservations = Reservation::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                  ->orWhere(function($sub) use ($start, $end) {
                      $sub->whereDate('check_in', '<=', $end->toDateString())
                          ->whereDate('check_out', '>=', $start->toDateString());
                  });
            })
            ->get();

        $occupiedRoomNights    = 0;
        $totalRoomRevenueMinor = 0;
        $totalTaxMinor         = 0;
        $totalFeeMinor         = 0;

        foreach ($reservations as $res) {
            $resStart = Carbon::parse($res->check_in);
            $resEnd   = Carbon::parse($res->check_out);

            $overlapStart = $resStart->greaterThan($start) ? $resStart : $start;
            $overlapEnd   = $resEnd->lessThan($end) ? $resEnd : $end;

            $overlapNights = max(1, $overlapStart->diffInDays($overlapEnd));
            $occupiedRoomNights += ($overlapNights * max(1, $res->rooms_count));

            $totalRoomRevenueMinor += ($res->subtotal_minor > 0 ? $res->subtotal_minor : $res->total_minor);
            $totalTaxMinor         += $res->tax_minor;
            $totalFeeMinor         += $res->fee_minor;
        }

        $totalGrossRevenueMinor = $totalRoomRevenueMinor + $totalTaxMinor + $totalFeeMinor;

        $occupancyPct = $totalAvailableRoomNights > 0
            ? round(($occupiedRoomNights / $totalAvailableRoomNights) * 100, 1)
            : 0.0;

        $adrMinor = $occupiedRoomNights > 0
            ? (int) round($totalRoomRevenueMinor / $occupiedRoomNights)
            : 0;

        $revparMinor = $totalAvailableRoomNights > 0
            ? (int) round($totalRoomRevenueMinor / $totalAvailableRoomNights)
            : 0;

        // Room Type Breakdown Performance
        $roomTypePerformance = DB::table('reservation_rooms')
            ->join('reservations', 'reservation_rooms.reservation_id', '=', 'reservations.id')
            ->join('room_types', 'reservation_rooms.room_type_id', '=', 'room_types.id')
            ->where('reservations.property_id', $property->id)
            ->where('reservations.status', '!=', 'cancelled')
            ->select(
                'room_types.name as room_type_name',
                DB::raw('count(reservation_rooms.id) as bookings_count'),
                DB::raw('sum(reservations.total_minor) as total_revenue_minor')
            )
            ->groupBy('room_types.name')
            ->get();

        return [
            'start_date'                 => $start->toDateString(),
            'end_date'                   => $end->toDateString(),
            'days_count'                 => $daysCount,
            'total_rooms'                => $totalRooms,
            'total_available_room_nights'=> $totalAvailableRoomNights,
            'occupied_room_nights'       => $occupiedRoomNights,
            'occupancy_pct'              => $occupancyPct,
            'total_room_revenue_minor'   => $totalRoomRevenueMinor,
            'total_tax_minor'            => $totalTaxMinor,
            'total_fee_minor'            => $totalFeeMinor,
            'total_gross_revenue_minor'  => $totalGrossRevenueMinor,
            'adr_minor'                  => $adrMinor,
            'revpar_minor'               => $revparMinor,
            'reservations_count'         => $reservations->count(),
            'currency'                   => $property->currency ?: 'USD',
            'room_type_performance'      => $roomTypePerformance,
        ];
    }

    /**
     * Compute daily time series data for Chart.js graphs.
     */
    public function getTimeSeriesTrends(Property $property, string $startDate, string $endDate): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $totalRooms = max(1, Room::where('property_id', $property->id)->count());

        $labels         = [];
        $occupancyData  = [];
        $revenueData    = [];
        $adrData        = [];
        $revparData     = [];

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $labels[] = $date->format('M d');

            // Active reservations/stays on this date
            $activeReservations = Reservation::where('property_id', $property->id)
                ->where('status', '!=', 'cancelled')
                ->whereDate('check_in', '<=', $dateStr)
                ->whereDate('check_out', '>=', $dateStr)
                ->get();

            $stayCount = Stay::where('property_id', $property->id)
                ->whereDate('arrival_date', '<=', $dateStr)
                ->whereDate('departure_date', '>=', $dateStr)
                ->where('status', '!=', 'cancelled')
                ->count();

            $occupiedCount = max($activeReservations->count(), $stayCount);

            $dailyRevenueMinor = 0;
            foreach ($activeReservations as $res) {
                $dailyRate = $res->nights > 0 ? ($res->subtotal_minor / $res->nights) : ($res->subtotal_minor ?: $res->total_minor);
                $dailyRevenueMinor += $dailyRate;
            }

            $occPct = round(($occupiedCount / $totalRooms) * 100, 1);
            $adr    = $occupiedCount > 0 ? round(($dailyRevenueMinor / 100) / $occupiedCount, 2) : 0;
            $revpar = round(($dailyRevenueMinor / 100) / $totalRooms, 2);

            $occupancyData[] = min(100, $occPct);
            $revenueData[]   = round($dailyRevenueMinor / 100, 2);
            $adrData[]       = $adr;
            $revparData[]    = $revpar;
        }

        return [
            'labels'    => $labels,
            'occupancy' => $occupancyData,
            'revenue'   => $revenueData,
            'adr'       => $adrData,
            'revpar'    => $revparData,
        ];
    }

    /**
     * Compute distribution by booking channel / source.
     */
    public function getBookingSourceDistribution(Property $property, string $startDate, string $endDate): array
    {
        $reservations = Reservation::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
                  ->orWhere(function($sub) use ($startDate, $endDate) {
                      $sub->whereDate('check_in', '<=', $endDate)
                          ->whereDate('check_out', '>=', $startDate);
                  });
            })
            ->with('bookingSource')
            ->get();

        $channels = [];
        foreach ($reservations as $res) {
            $sourceName = ucfirst(str_replace(['_', '-'], ' ', $res->bookingSource?->name ?: ($res->booking_channel ?: 'Direct Web')));
            if (!isset($channels[$sourceName])) {
                $channels[$sourceName] = [
                    'name'          => $sourceName,
                    'count'         => 0,
                    'revenue_minor' => 0,
                ];
            }
            $channels[$sourceName]['count']++;
            $channels[$sourceName]['revenue_minor'] += $res->total_minor;
        }

        return array_values($channels);
    }
}
