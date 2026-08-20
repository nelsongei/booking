<?php

namespace App\Domain\Reporting\Contracts;

use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;

interface CompliancePackInterface
{
    public function getCountryCode(): string;

    public function calculateTaxesAndLevies(Property $property, int $baseAmountMinor): array;

    public function generateFiscalInvoicePayload(Reservation $reservation): array;

    public function generatePoliceGuestReport(Property $property, string $date): array;
}
