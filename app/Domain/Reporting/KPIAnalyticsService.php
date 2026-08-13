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

        $totalRooms = Room::where('property_id', $property->id)->count();
        $totalAvailableRoomNights = $totalRooms * $daysCount;

        // Query active reservations within date range
        $reservations = Reservation::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where('check_in', '<=', $end->toDateString())
            ->where('check_out', '>=', $start->toDateString())
            ->get();

        $occupiedRoomNights = 0;
        $totalRoomRevenueMinor = 0;
        $totalTaxMinor = 0;
        $totalFeeMinor = 0;

        foreach ($reservations as $res) {
            // Calculate overlapping nights within start/end range
            $resStart = Carbon::parse($res->check_in);
            $resEnd   = Carbon::parse($res->check_out);

            $overlapStart = $resStart->greaterThan($start) ? $resStart : $start;
            $overlapEnd   = $resEnd->lessThan($end) ? $resEnd : $end;

            $overlapNights = max(0, $overlapStart->diffInDays($overlapEnd));
            $occupiedRoomNights += ($overlapNights * max(1, $res->rooms_count));

            // Pro-rate revenue if reservation spans outside date filter range
            $ratio = $res->nights > 0 ? ($overlapNights / $res->nights) : 1.0;
            $totalRoomRevenueMinor += (int) round($res->subtotal_minor * $ratio);
            $totalTaxMinor         += (int) round($res->tax_minor * $ratio);
            $totalFeeMinor         += (int) round($res->fee_minor * $ratio);
        }

        $totalGrossRevenueMinor = $totalRoomRevenueMinor + $totalTaxMinor + $totalFeeMinor;

        $occupancyPct = $totalAvailableRoomNights > 0
            ? round(($occupiedRoomNights / $totalAvailableRoomNights) * 100, 2)
            : 0.0;

        $adrMinor = $occupiedRoomNights > 0
            ? (int) round($totalRoomRevenueMinor / $occupiedRoomNights)
            : 0;

        $revparMinor = $totalAvailableRoomNights > 0
            ? (int) round($totalRoomRevenueMinor / $totalAvailableRoomNights)
            : 0;

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
            'currency'                   => $property->currency,
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

            // Count occupied stays on this night
            $occupied = Stay::where('property_id', $property->id)
                ->where('arrival_date', '<=', $dateStr)
                ->where('departure_date', '>', $dateStr)
                ->where('status', 'checked_in')
                ->count();

            // Daily reservations active on this night
            $activeReservations = Reservation::where('property_id', $property->id)
                ->where('status', '!=', 'cancelled')
                ->where('check_in', '<=', $dateStr)
                ->where('check_out', '>', $dateStr)
                ->get();

            $dailyRevenueMinor = 0;
            foreach ($activeReservations as $res) {
                $dailyRate = $res->nights > 0 ? ($res->subtotal_minor / $res->nights) : 0;
                $dailyRevenueMinor += $dailyRate;
            }

            $occPct = round(($occupied / $totalRooms) * 100, 1);
            $adr    = $occupied > 0 ? round(($dailyRevenueMinor / 100) / $occupied, 2) : 0;
            $revpar = round(($dailyRevenueMinor / 100) / $totalRooms, 2);

            $occupancyData[] = $occPct;
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
            ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->with('bookingSource')
            ->get();

        $channels = [];
        foreach ($reservations as $res) {
            $sourceName = $res->bookingSource?->name ?: ($res->booking_channel ?: 'Direct / Staff');
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
