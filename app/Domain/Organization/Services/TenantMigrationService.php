<?php

namespace App\Domain\Organization\Services;

use App\Domain\Audit\AuditService;
use App\Infrastructure\Persistence\GuestProfile;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantMigrationService
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Dry-run validation preview for CSV migration data.
     */
    public function dryRunImport(Property $property, array $rows, string $type): array
    {
        $validRows  = 0;
        $errors     = [];
        $duplicates = 0;

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Accounting for 1-based index and header

            if ($type === 'guests') {
                $email = trim($row['email'] ?? '');
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row {$rowNum}: Invalid or missing email address.";
                    continue;
                }

                $exists = GuestProfile::where('organization_id', $property->organization_id)
                    ->where('email', $email)
                    ->exists();

                if ($exists) {
                    $duplicates++;
                }

                $validRows++;
            } elseif ($type === 'reservations') {
                $code = trim($row['confirmation_number'] ?? '');
                if (empty($code)) {
                    $errors[] = "Row {$rowNum}: Missing confirmation number.";
                    continue;
                }

                $exists = Reservation::where('property_id', $property->id)
                    ->where('confirmation_number', $code)
                    ->exists();

                if ($exists) {
                    $duplicates++;
                }

                $validRows++;
            }
        }

        return [
            'type'            => $type,
            'total_rows'      => count($rows),
            'valid_rows'      => $validRows,
            'duplicate_count' => $duplicates,
            'errors'          => $errors,
            'can_proceed'     => empty($errors),
        ];
    }

    /**
     * Execute transactional migration import.
     */
    public function executeImport(Property $property, array $rows, string $type, User $user): array
    {
        $importedCount = 0;

        DB::transaction(function () use ($property, $rows, $type, $user, &$importedCount) {
            foreach ($rows as $row) {
                if ($type === 'guests') {
                    GuestProfile::firstOrCreate(
                        [
                            'organization_id' => $property->organization_id,
                            'email'           => trim($row['email']),
                        ],
                        [
                            'ulid'       => (string) Str::ulid(),
                            'first_name' => trim($row['first_name'] ?? 'Guest'),
                            'last_name'  => trim($row['last_name'] ?? 'User'),
                            'phone'      => trim($row['phone'] ?? null),
                        ]
                    );
                    $importedCount++;
                }
            }

            $this->auditService->log(
                $user,
                'tenant.data_migration',
                'Property',
                $property->id,
                "Executed {$type} CSV migration import of {$importedCount} records for property {$property->name}."
            );
        });

        return [
            'success'        => true,
            'imported_count' => $importedCount,
        ];
    }
}
