<?php

namespace App\Domain\Folios;

use App\Infrastructure\Persistence\CashierShift;
use App\Infrastructure\Persistence\CashTransaction;
use App\Infrastructure\Persistence\ChargeCode;
use App\Infrastructure\Persistence\FolioAccount;
use App\Infrastructure\Persistence\FolioTransaction;
use App\Infrastructure\Persistence\FolioWindow;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FolioLedgerService
{
    /**
     * Get existing guest folio or create a new one with default Window 1 (Main).
     */
    public function getOrCreateFolio(Reservation $reservation): FolioAccount
    {
        return DB::transaction(function () use ($reservation) {
            $folio = FolioAccount::where('reservation_id', $reservation->id)
                ->where('type', 'guest')
                ->first();

            if (!$folio) {
                $folio = FolioAccount::create([
                    'ulid'           => (string) Str::ulid(),
                    'reservation_id' => $reservation->id,
                    'property_id'    => $reservation->property_id,
                    'type'           => 'guest',
                    'status'         => 'open',
                    'currency'       => $reservation->currency,
                ]);

                // Create default Window 1
                FolioWindow::create([
                    'folio_account_id' => $folio->id,
                    'name'             => 'Main Window',
                    'window_number'    => 1,
                    'is_active'        => true,
                ]);

                // Create optional Window 2 for extras/company routing
                FolioWindow::create([
                    'folio_account_id' => $folio->id,
                    'name'             => 'Extras Window',
                    'window_number'    => 2,
                    'is_active'        => true,
                ]);
            }

            return $folio;
        });
    }

    /**
     * Post a charge transaction (positive amount).
     */
    public function postCharge(
        FolioAccount $folio,
        ChargeCode $chargeCode,
        int $amountMinor,
        string $description,
        ?int $windowId = null,
        ?User $staff = null
    ): FolioTransaction {
        $window = $windowId ? FolioWindow::find($windowId) : $folio->windows()->first();

        return FolioTransaction::create([
            'ulid'             => (string) Str::ulid(),
            'folio_account_id' => $folio->id,
            'folio_window_id'  => $window?->id,
            'property_id'      => $folio->property_id,
            'type'             => 'charge',
            'charge_code_id'   => $chargeCode->id,
            'description'      => $description,
            'amount_minor'     => abs($amountMinor), // Positive = Charge
            'currency'         => $folio->currency,
            'posted_by'        => $staff?->id ?: auth()->id(),
            'posted_at'        => now(),
            'business_date'    => now()->toDateString(),
        ]);
    }

    /**
     * Post a payment / credit transaction (negative amount).
     */
    public function postPayment(
        FolioAccount $folio,
        int $amountMinor,
        string $provider = 'cash',
        ?string $reference = null,
        ?int $windowId = null,
        ?User $staff = null
    ): FolioTransaction {
        $window = $windowId ? FolioWindow::find($windowId) : $folio->windows()->first();

        return FolioTransaction::create([
            'ulid'             => (string) Str::ulid(),
            'folio_account_id' => $folio->id,
            'folio_window_id'  => $window?->id,
            'property_id'      => $folio->property_id,
            'type'             => 'payment',
            'description'      => 'Payment received via ' . strtoupper($provider),
            'amount_minor'     => -abs($amountMinor), // Negative = Payment/Credit
            'currency'         => $folio->currency,
            'reference'        => $reference,
            'posted_by'        => $staff?->id ?: auth()->id(),
            'posted_at'        => now(),
            'business_date'    => now()->toDateString(),
        ]);
    }

    /**
     * Reverse a transaction by appending an exact inverse entry (APPEND-ONLY).
     */
    public function reverseTransaction(
        FolioTransaction $transaction,
        string $reason = 'Correction',
        ?User $staff = null
    ): FolioTransaction {
        return DB::transaction(function () use ($transaction, $reason, $staff) {
            $invertedAmount = -$transaction->amount_minor;

            return FolioTransaction::create([
                'ulid'                    => (string) Str::ulid(),
                'folio_account_id'        => $transaction->folio_account_id,
                'folio_window_id'         => $transaction->folio_window_id,
                'property_id'             => $transaction->property_id,
                'type'                    => 'reversal',
                'charge_code_id'          => $transaction->charge_code_id,
                'description'             => 'Reversal: ' . $transaction->description,
                'amount_minor'            => $invertedAmount,
                'currency'                => $transaction->currency,
                'reverses_transaction_id' => $transaction->id,
                'reversal_reason'         => $reason,
                'posted_by'               => $staff?->id ?: auth()->id(),
                'posted_at'               => now(),
                'business_date'           => now()->toDateString(),
            ]);
        });
    }

    /**
     * Open a cashier shift for a user.
     */
    public function openCashierShift(Property $property, User $user, int $openingBalanceMinor): CashierShift
    {
        return CashierShift::create([
            'property_id'           => $property->id,
            'user_id'               => $user->id,
            'status'                => 'open',
            'opening_balance_minor' => $openingBalanceMinor,
            'opened_at'             => now(),
        ]);
    }

    /**
     * Close a cashier shift and compute expected closing balance and variance.
     */
    public function closeCashierShift(CashierShift $shift, int $actualClosingMinor, ?string $notes = null): CashierShift
    {
        $netCashReceived = CashTransaction::where('cashier_shift_id', $shift->id)
            ->where('type', 'receive')
            ->sum('amount_minor');

        $netCashPayouts = CashTransaction::where('cashier_shift_id', $shift->id)
            ->where('type', 'payout')
            ->sum('amount_minor');

        $expectedClosing = $shift->opening_balance_minor + $netCashReceived - $netCashPayouts;
        $variance        = $actualClosingMinor - $expectedClosing;

        $shift->update([
            'status'                 => 'closed',
            'closing_balance_minor'  => $actualClosingMinor,
            'expected_closing_minor' => $expectedClosing,
            'variance_minor'         => $variance,
            'notes'                  => $notes,
            'closed_at'              => now(),
        ]);

        return $shift;
    }
}
