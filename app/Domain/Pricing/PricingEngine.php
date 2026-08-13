<?php

namespace App\Domain\Pricing;

use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\RateDay;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\RoomType;
use App\Infrastructure\Persistence\Tax;
use Carbon\Carbon;

class PricingEngine
{
    /**
     * Calculate stay pricing with itemized daily breakdown, extra guest charges, and taxes.
     */
    public function calculate(
        Property $property,
        RoomType $roomType,
        RatePlan $ratePlan,
        string $checkIn,
        string $checkOut,
        int $adults = 2,
        int $children = 0
    ): array {
        $startDate = Carbon::parse($checkIn);
        $endDate   = Carbon::parse($checkOut);
        $nights    = $startDate->diffInDays($endDate);

        $nightlyBreakdown = [];
        $subtotalMinor    = 0;
        $trace            = [];

        // Fetch configured taxes
        $taxes = Tax::where('property_id', $property->id)->where('is_active', true)->get();

        for ($d = $startDate->copy(); $d->lt($endDate); $d->addDay()) {
            $dateStr = $d->toDateString();

            $rateDay = RateDay::where('property_id', $property->id)
                ->where('rate_plan_id', $ratePlan->id)
                ->where('room_type_id', $roomType->id)
                ->where('date', $dateStr)
                ->first();

            // Default fallback rate if rateDay not set yet
            $baseRateMinor = $rateDay ? $rateDay->amount_minor : 10000; // default fallback 100.00
            $extraAdults   = max(0, $adults - $roomType->base_occupancy);
            $extraChildren = max(0, $children);

            $extraAdultMinor  = $rateDay ? ($rateDay->extra_adult_minor * $extraAdults) : 0;
            $extraChildMinor  = $rateDay ? ($rateDay->extra_child_minor * $extraChildren) : 0;
            $nightTotalMinor  = $baseRateMinor + $extraAdultMinor + $extraChildMinor;

            $subtotalMinor += $nightTotalMinor;

            $nightlyBreakdown[] = [
                'date'              => $dateStr,
                'base_rate_minor'   => $baseRateMinor,
                'extra_adult_minor' => $extraAdultMinor,
                'extra_child_minor' => $extraChildMinor,
                'night_total_minor' => $nightTotalMinor,
                'currency'          => $ratePlan->currency,
            ];

            $trace[] = "Date {$dateStr}: Base={$baseRateMinor}, ExtraAdult={$extraAdultMinor}, ExtraChild={$extraChildMinor} -> Total={$nightTotalMinor}";
        }

        // Calculate Taxes
        $taxTotalMinor = 0;
        $taxBreakdown  = [];

        foreach ($taxes as $tax) {
            $taxAmountMinor = 0;

            if ($tax->type === 'percentage') {
                $taxAmountMinor = (int) round(($subtotalMinor * $tax->rate) / 100);
            } elseif ($tax->type === 'fixed_per_night') {
                $taxAmountMinor = (int) round($tax->rate * 100 * $nights);
            } elseif ($tax->type === 'fixed_per_stay') {
                $taxAmountMinor = (int) round($tax->rate * 100);
            } elseif ($tax->type === 'fixed_per_person') {
                $taxAmountMinor = (int) round($tax->rate * 100 * ($adults + $children) * $nights);
            }

            if (!$tax->is_included_in_rate) {
                $taxTotalMinor += $taxAmountMinor;
            }

            $taxBreakdown[] = [
                'name'         => $tax->name,
                'code'         => $tax->code,
                'rate'         => $tax->rate,
                'type'         => $tax->type,
                'is_included'  => $tax->is_included_in_rate,
                'amount_minor' => $taxAmountMinor,
            ];
        }

        $grandTotalMinor = $subtotalMinor + $taxTotalMinor;

        return [
            'property_id'     => $property->id,
            'room_type_id'    => $roomType->id,
            'rate_plan_id'    => $ratePlan->id,
            'check_in'        => $checkIn,
            'check_out'       => $checkOut,
            'nights'          => $nights,
            'guests'          => ['adults' => $adults, 'children' => $children],
            'currency'        => $ratePlan->currency,
            'subtotal_minor'  => $subtotalMinor,
            'tax_total_minor' => $taxTotalMinor,
            'total_minor'     => $grandTotalMinor,
            'nightly'         => $nightlyBreakdown,
            'taxes'           => $taxBreakdown,
            'trace'           => $trace,
        ];
    }
}
