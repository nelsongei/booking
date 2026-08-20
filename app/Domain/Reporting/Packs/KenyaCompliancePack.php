<?php

namespace App\Domain\Reporting\Packs;

use App\Domain\Reporting\Contracts\CompliancePackInterface;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Support\Str;

class KenyaCompliancePack implements CompliancePackInterface
{
    public function getCountryCode(): string
    {
        return 'KE';
    }

    public function calculateTaxesAndLevies(Property $property, int $baseAmountMinor): array
    {
        $vatMinor         = (int) round($baseAmountMinor * 0.16); // 16% Kenya VAT
        $tourismLevyMinor = (int) round($baseAmountMinor * 0.02); // 2% Tourism Levy

        return [
            'vat_minor'          => $vatMinor,
            'tourism_levy_minor' => $tourismLevyMinor,
            'total_tax_minor'    => $vatMinor + $tourismLevyMinor,
        ];
    }

    public function generateFiscalInvoicePayload(Reservation $reservation): array
    {
        return [
            'country'              => 'KE',
            'system_provider'      => 'KRA_ETIMS',
            'cu_serial_number'     => 'KRA' . rand(100000, 999999),
            'invoice_number'       => 'KE-INV-' . $reservation->confirmation_number,
            'taxable_amount_minor' => $reservation->subtotal_minor,
            'vat_amount_minor'     => (int) round($reservation->subtotal_minor * 0.16),
            'qr_code_signature'    => 'https://etims.kra.go.ke/verify?num=' . Str::random(16),
        ];
    }

    public function generatePoliceGuestReport(Property $property, string $date): array
    {
        return [
            'country'            => 'KE',
            'authority'          => 'Kenya Police Service - Department of Immigration',
            'property_name'      => $property->name,
            'report_date'        => $date,
            'guest_entry_format' => 'CSV_IMM_KE_V1',
        ];
    }
}
