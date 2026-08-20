<?php

namespace App\Domain\Pricing\Services;

use App\Infrastructure\Persistence\RoomType;

class RateRecommendationService
{
    /**
     * Recommend dynamic BAR rate for a room type bounded by strict floor/ceiling guardrails.
     */
    public function recommendRate(
        RoomType $roomType,
        float $occupancyPct,
        int $priceFloorMinor,
        int $priceCeilingMinor
    ): array {
        $basePrice = $roomType->base_price_minor;

        // Dynamic multiplier based on occupancy thresholds
        $multiplier = 1.0;
        if ($occupancyPct >= 90.0) {
            $multiplier = 1.35;
        } elseif ($occupancyPct >= 75.0) {
            $multiplier = 1.20;
        } elseif ($occupancyPct >= 50.0) {
            $multiplier = 1.05;
        } else {
            $multiplier = 0.90;
        }

        $recommended = (int) round($basePrice * $multiplier);

        // Apply strict guardrails
        $bounded = max($priceFloorMinor, min($priceCeilingMinor, $recommended));

        return [
            'room_type_id'      => $roomType->id,
            'base_price_minor'  => $basePrice,
            'occupancy_pct'     => $occupancyPct,
            'multiplier'        => $multiplier,
            'recommended_minor' => $bounded,
            'price_floor_minor' => $priceFloorMinor,
            'price_ceiling_minor'=> $priceCeilingMinor,
            'is_bounded'        => ($bounded !== $recommended),
        ];
    }
}
