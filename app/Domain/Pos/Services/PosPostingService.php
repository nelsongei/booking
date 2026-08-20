<?php

namespace App\Domain\Pos\Services;

use App\Domain\Folios\FolioLedgerService;
use App\Domain\Pos\Models\PosOrder;
use App\Infrastructure\Persistence\ChargeCode;
use App\Infrastructure\Persistence\User;
use Illuminate\Support\Facades\DB;

class PosPostingService
{
    protected FolioLedgerService $ledgerService;

    public function __construct(FolioLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Post a POS outlet order directly to a guest's room folio.
     */
    public function postOrderToRoom(PosOrder $order, User $user): void
    {
        if (!$order->reservation_id) {
            throw new \InvalidArgumentException("POS order {$order->ulid} does not have an associated reservation.");
        }

        DB::transaction(function () use ($order, $user) {
            $reservation = $order->reservation;
            $folio       = $this->ledgerService->getOrCreateFolio($reservation);

            $chargeCode = ChargeCode::where('property_id', $order->property_id)
                ->where('category', 'food_beverage')
                ->first()
                ?? ChargeCode::where('property_id', $order->property_id)->first();

            if (!$chargeCode) {
                $chargeCode = ChargeCode::create([
                    'property_id'      => $order->property_id,
                    'code'             => 'POS-FB',
                    'name'             => 'Food & Beverage Room Charge',
                    'category'         => 'food_beverage',
                    'revenue_category' => 'food_beverage',
                    'is_taxable'       => true,
                    'is_active'        => true,
                ]);
            }

            $description = "F&B POS Charge - Table {$order->table_number} ({$order->outlet->name})";

            $transaction = $this->ledgerService->postCharge(
                $folio,
                $chargeCode,
                $order->total_minor,
                $description,
                null,
                $user
            );

            $order->update([
                'status'           => 'billed',
                'payment_status'   => 'posted_to_room',
                'folio_account_id' => $folio->id,
            ]);
        });
    }
}
