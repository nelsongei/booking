<?php

namespace App\Domain\Pricing;

use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\RateQuote;
use App\Infrastructure\Persistence\RoomType;
use Illuminate\Support\Str;

class QuoteService
{
    protected PricingEngine $pricingEngine;

    public function __construct(PricingEngine $pricingEngine)
    {
        $this->pricingEngine = $pricingEngine;
    }

    /**
     * Generate and store an itemized price quote.
     */
    public function generateQuote(
        Property $property,
        RoomType $roomType,
        RatePlan $ratePlan,
        string $checkIn,
        string $checkOut,
        int $adults = 2,
        int $children = 0,
        ?string $promoCode = null
    ): RateQuote {
        $calculation = $this->pricingEngine->calculate($property, $roomType, $ratePlan, $checkIn, $checkOut, $adults, $children);

        return RateQuote::create([
            'ulid'        => (string) Str::ulid(),
            'property_id' => $property->id,
            'input'       => [
                'room_type_id' => $roomType->id,
                'rate_plan_id' => $ratePlan->id,
                'check_in'     => $checkIn,
                'check_out'    => $checkOut,
                'adults'       => $adults,
                'children'     => $children,
                'promo_code'   => $promoCode,
            ],
            'output'      => $calculation,
            'trace'       => $calculation['trace'],
            'promo_code'  => $promoCode,
            'currency'    => $calculation['currency'],
            'total_minor' => $calculation['total_minor'],
            'expires_at'  => now()->addMinutes(30),
        ]);
    }
}
