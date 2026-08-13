<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Folios\FolioLedgerService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\CashierShift;
use App\Infrastructure\Persistence\ChargeCode;
use App\Infrastructure\Persistence\FolioAccount;
use App\Infrastructure\Persistence\FolioTransaction;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Http\Request;

class FolioController extends Controller
{
    protected FolioLedgerService $ledgerService;

    public function __construct(FolioLedgerService $ledgerService)
    {
        $this->middleware('auth');
        $this->ledgerService = $ledgerService;
    }

    protected function resolveCurrentProperty(): ?Property
    {
        return app()->bound('current.property') ? app('current.property') : Property::first();
    }

    /**
     * Master Folios Roster View.
     */
    public function index(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $folios = $property ? FolioAccount::where('property_id', $property->id)
            ->with(['reservation.primaryGuest', 'transactions'])
            ->get() : collect();

        return view('admin.folios.index', compact('property', 'folios'));
    }

    /**
     * Detailed Folio Ledger View.
     */
    public function show(FolioAccount $folio)
    {
        $folio->load(['reservation.primaryGuest', 'property', 'windows', 'transactions.chargeCode', 'transactions.postedBy']);

        $chargeCodes = ChargeCode::where('property_id', $folio->property_id)
            ->where('is_active', true)
            ->get();

        return view('admin.folios.show', compact('folio', 'chargeCodes'));
    }

    /**
     * Post a charge transaction.
     */
    public function postCharge(Request $request, FolioAccount $folio)
    {
        $request->validate([
            'charge_code_id' => 'required|exists:charge_codes,id',
            'amount'         => 'required|numeric|min:0.01',
            'description'    => 'required|string|max:255',
            'window_id'      => 'nullable|exists:folio_windows,id',
        ]);

        $chargeCode  = ChargeCode::findOrFail($request->input('charge_code_id'));
        $amountMinor = (int) round($request->input('amount') * 100);

        try {
            $this->ledgerService->postCharge(
                $folio,
                $chargeCode,
                $amountMinor,
                $request->input('description'),
                $request->input('window_id'),
                auth()->user()
            );

            return redirect()->back()->with('success', 'Charge posted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Posting failed: ' . $e->getMessage());
        }
    }

    /**
     * Post a payment / credit transaction.
     */
    public function postPayment(Request $request, FolioAccount $folio)
    {
        $request->validate([
            'amount'    => 'required|numeric|min:0.01',
            'provider'  => 'required|string',
            'reference' => 'nullable|string',
            'window_id' => 'nullable|exists:folio_windows,id',
        ]);

        $amountMinor = (int) round($request->input('amount') * 100);

        try {
            $this->ledgerService->postPayment(
                $folio,
                $amountMinor,
                $request->input('provider', 'cash'),
                $request->input('reference'),
                $request->input('window_id'),
                auth()->user()
            );

            return redirect()->back()->with('success', 'Payment posted to folio.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Payment posting failed: ' . $e->getMessage());
        }
    }

    /**
     * Append a transaction reversal (APPEND-ONLY).
     */
    public function reverse(Request $request, FolioTransaction $transaction)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        try {
            $this->ledgerService->reverseTransaction($transaction, $request->input('reason'), auth()->user());
            return redirect()->back()->with('success', 'Transaction reversed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Reversal failed: ' . $e->getMessage());
        }
    }

    /**
     * Cashier Shifts Dashboard View.
     */
    public function shiftsIndex(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $openShift = $property ? CashierShift::where('property_id', $property->id)
            ->where('user_id', auth()->id())
            ->where('status', 'open')
            ->first() : null;

        $shifts = $property ? CashierShift::where('property_id', $property->id)
            ->with(['user', 'cashTransactions'])
            ->latest()
            ->get() : collect();

        return view('admin.folios.shifts', compact('property', 'openShift', 'shifts'));
    }

    /**
     * Open a new Cashier Shift.
     */
    public function openShift(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $openingMinor = (int) round($request->input('opening_balance') * 100);

        try {
            $this->ledgerService->openCashierShift($property, auth()->user(), $openingMinor);
            return redirect()->back()->with('success', 'Cashier shift opened with float ' . number_format($openingMinor / 100, 2) . ' ' . $property->currency);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to open shift: ' . $e->getMessage());
        }
    }

    /**
     * Close an active Cashier Shift.
     */
    public function closeShift(Request $request, CashierShift $shift)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        $closingMinor = (int) round($request->input('closing_balance') * 100);

        try {
            $this->ledgerService->closeCashierShift($shift, $closingMinor, $request->input('notes'));
            return redirect()->back()->with('success', 'Shift closed successfully. Calculated variance: ' . number_format($shift->variance_minor / 100, 2));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to close shift: ' . $e->getMessage());
        }
    }
}
