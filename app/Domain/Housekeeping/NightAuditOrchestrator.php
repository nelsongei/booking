<?php

namespace App\Domain\Housekeeping;

use App\Domain\Folios\FolioLedgerService;
use App\Infrastructure\Persistence\BusinessDateHistory;
use App\Infrastructure\Persistence\CashierShift;
use App\Infrastructure\Persistence\ChargeCode;
use App\Infrastructure\Persistence\NightAudit;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\PropertySetting;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\Stay;
use App\Infrastructure\Persistence\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NightAuditOrchestrator
{
    protected FolioLedgerService $folioLedgerService;

    public function __construct(FolioLedgerService $folioLedgerService)
    {
        $this->folioLedgerService = $folioLedgerService;
    }

    /**
     * Get the current business date for a property.
     */
    public function getBusinessDate(Property $property): string
    {
        $setting = PropertySetting::where('property_id', $property->id)
            ->where('key', 'business_date')
            ->first();

        return $setting ? $setting->value : now()->toDateString();
    }

    /**
     * Run pre-audit validations. Returns stats & any blocking/warning issues.
     */
    public function validatePreConditions(Property $property, string $businessDate): array
    {
        $pendingArrivals = Reservation::where('property_id', $property->id)
            ->where('check_in', $businessDate)
            ->whereIn('status', ['confirmed', 'held'])
            ->count();

        $pendingDepartures = Stay::where('property_id', $property->id)
            ->where('departure_date', $businessDate)
            ->where('status', 'checked_in')
            ->count();

        $openCashierShifts = CashierShift::where('property_id', $property->id)
            ->where('status', 'open')
            ->count();

        $inHouseStays = Stay::where('property_id', $property->id)
            ->where('status', 'checked_in')
            ->count();

        $canProceed = ($pendingArrivals === 0 && $pendingDepartures === 0 && $openCashierShifts === 0);

        return [
            'can_proceed'          => $canProceed,
            'pending_arrivals'     => $pendingArrivals,
            'pending_departures'   => $pendingDepartures,
            'open_cashier_shifts'  => $openCashierShifts,
            'in_house_stays'       => $inHouseStays,
        ];
    }

    /**
     * Execute or resume night audit step by step.
     */
    public function executeAudit(Property $property, ?User $user = null): NightAudit
    {
        $businessDate = $this->getBusinessDate($property);

        $audit = NightAudit::firstOrCreate(
            [
                'property_id'   => $property->id,
                'business_date' => $businessDate,
            ],
            [
                'status'     => 'in_progress',
                'started_by' => $user?->id ?: auth()->id(),
                'started_at' => now(),
                'steps'      => [
                    'validate'          => ['status' => 'pending', 'message' => 'Validating operations'],
                    'post_room_charges' => ['status' => 'pending', 'message' => 'Posting daily room rates'],
                    'update_no_shows'   => ['status' => 'pending', 'message' => 'Processing no-shows'],
                    'roll_date'         => ['status' => 'pending', 'message' => 'Advancing business date'],
                    'generate_report'   => ['status' => 'pending', 'message' => 'Generating end-of-day summary'],
                ],
            ]
        );

        if ($audit->status === 'completed') {
            return $audit;
        }

        $audit->update(['status' => 'in_progress']);
        $steps = $audit->steps ?: [];

        try {
            // Step 1: Validate
            if (($steps['validate']['status'] ?? 'pending') !== 'done') {
                $validation = $this->validatePreConditions($property, $businessDate);
                $steps['validate'] = [
                    'status'       => 'done',
                    'message'      => 'Pre-check completed',
                    'completed_at' => now()->toIso8601String(),
                    'details'      => $validation,
                ];
                $audit->update(['steps' => $steps]);
            }

            // Step 2: Post Room Charges
            if (($steps['post_room_charges']['status'] ?? 'pending') !== 'done') {
                $postedCount = $this->postDailyRoomCharges($property, $businessDate, $user);
                $steps['post_room_charges'] = [
                    'status'       => 'done',
                    'message'      => "Posted room charges for {$postedCount} in-house stay(s)",
                    'completed_at' => now()->toIso8601String(),
                    'posted_count' => $postedCount,
                ];
                $audit->update(['steps' => $steps]);
            }

            // Step 3: Update No-Shows
            if (($steps['update_no_shows']['status'] ?? 'pending') !== 'done') {
                $noShowCount = $this->processNoShows($property, $businessDate);
                $steps['update_no_shows'] = [
                    'status'       => 'done',
                    'message'      => "Marked {$noShowCount} unhandled arrival(s) as no-show",
                    'completed_at' => now()->toIso8601String(),
                    'no_show_count'=> $noShowCount,
                ];
                $audit->update(['steps' => $steps]);
            }

            // Step 4: Roll Date
            if (($steps['roll_date']['status'] ?? 'pending') !== 'done') {
                $newDate = $this->rollBusinessDate($property, $businessDate, $user);
                $steps['roll_date'] = [
                    'status'       => 'done',
                    'message'      => "Business date rolled from {$businessDate} to {$newDate}",
                    'completed_at' => now()->toIso8601String(),
                    'new_date'     => $newDate,
                ];
                $audit->update(['steps' => $steps]);
            }

            // Step 5: Generate Audit Report
            if (($steps['generate_report']['status'] ?? 'pending') !== 'done') {
                $reportData = $this->compileAuditReport($property, $businessDate);
                $steps['generate_report'] = [
                    'status'       => 'done',
                    'message'      => 'Audit report compiled successfully',
                    'completed_at' => now()->toIso8601String(),
                ];

                $audit->update([
                    'status'       => 'completed',
                    'steps'        => $steps,
                    'report_data'  => $reportData,
                    'completed_by' => $user?->id ?: auth()->id(),
                    'completed_at' => now(),
                ]);
            }

            return $audit;
        } catch (\Exception $e) {
            $audit->update([
                'status'        => 'failed',
                'failure_notes' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Post room charges for all active in-house stays.
     */
    protected function postDailyRoomCharges(Property $property, string $businessDate, ?User $user = null): int
    {
        $roomChargeCode = ChargeCode::firstOrCreate(
            ['property_id' => $property->id, 'code' => 'ROOM_CHARGE'],
            [
                'name'             => 'Daily Room Charge',
                'category'         => 'room',
                'revenue_category' => 'room_revenue',
                'is_taxable'       => true,
                'is_active'        => true,
            ]
        );

        $inHouseStays = Stay::where('property_id', $property->id)
            ->where('status', 'checked_in')
            ->with(['reservation.ratePlan', 'room'])
            ->get();

        $count = 0;
        foreach ($inHouseStays as $stay) {
            if (!$stay->reservation) {
                continue;
            }

            $folio = $this->folioLedgerService->getOrCreateFolio($stay->reservation);
            
            // Calculate daily room rate in minor units
            $dailyAmountMinor = (int) round(($stay->reservation->total_minor / max(1, $stay->reservation->nights)));
            $description      = "Nightly Room Charge - Room " . ($stay->room?->room_number ?: 'N/A') . " (" . $businessDate . ")";

            $this->folioLedgerService->postCharge(
                $folio,
                $roomChargeCode,
                $dailyAmountMinor,
                $description,
                null,
                $user
            );

            $count++;
        }

        return $count;
    }

    /**
     * Process no-show reservations.
     */
    protected function processNoShows(Property $property, string $businessDate): int
    {
        $unhandledArrivals = Reservation::where('property_id', $property->id)
            ->where('check_in', $businessDate)
            ->whereIn('status', ['confirmed', 'held'])
            ->get();

        $count = 0;
        foreach ($unhandledArrivals as $reservation) {
            $reservation->update(['status' => 'no_show']);
            $count++;
        }

        return $count;
    }

    /**
     * Advance business date setting and log history.
     */
    protected function rollBusinessDate(Property $property, string $currentDate, ?User $user = null): string
    {
        $nextDate = Carbon::parse($currentDate)->addDay()->toDateString();

        PropertySetting::updateOrCreate(
            ['property_id' => $property->id, 'key' => 'business_date'],
            ['value' => $nextDate]
        );

        BusinessDateHistory::create([
            'property_id'            => $property->id,
            'business_date'          => $nextDate,
            'previous_business_date' => $currentDate,
            'rolled_by'              => $user?->id ?: auth()->id(),
            'rolled_at'              => now(),
        ]);

        return $nextDate;
    }

    /**
     * Compile audit summary report.
     */
    protected function compileAuditReport(Property $property, string $businessDate): array
    {
        $totalRooms = Room::where('property_id', $property->id)->count();
        $occupied   = Stay::where('property_id', $property->id)->where('status', 'checked_in')->count();
        $occupancy  = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 2) : 0.0;

        return [
            'business_date'     => $businessDate,
            'total_rooms'       => $totalRooms,
            'occupied_rooms'    => $occupied,
            'occupancy_pct'     => $occupancy,
            'completed_at'      => now()->toDateTimeString(),
        ];
    }
}
