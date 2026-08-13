<?php

namespace App\Domain\Reservation;

use App\Infrastructure\Persistence\Property;
use Illuminate\Support\Str;

class ConfirmationNumberGenerator
{
    /**
     * Generate a human-readable unique confirmation number.
     * Example: SH01-202608-7K9A
     */
    public static function generate(Property $property): string
    {
        $prefix    = strtoupper(substr($property->code ?: 'RES', 0, 4));
        $yearMonth = now()->format('Ym');
        $random    = strtoupper(Str::random(4));

        return "{$prefix}-{$yearMonth}-{$random}";
    }
}
