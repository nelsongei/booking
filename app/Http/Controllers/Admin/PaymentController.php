<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Payments\InvoiceService;
use App\Domain\Payments\OfflinePaymentAdapter;
use App\Domain\Payments\StripeAdapter;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Invoice;
use App\Infrastructure\Persistence\Payment;
use App\Infrastructure\Persistence\Reservation;
use App\Mail\BookingConfirmationMail;
use App\Mail\PaymentReceiptMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected StripeAdapter $stripeAdapter;
    protected OfflinePaymentAdapter $offlineAdapter;
    protected InvoiceService $invoiceService;

    public function __construct(
        StripeAdapter $stripeAdapter,
        OfflinePaymentAdapter $offlineAdapter,
        InvoiceService $invoiceService
    ) {
        $this->stripeAdapter  = $stripeAdapter;
        $this->offlineAdapter = $offlineAdapter;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Process a manual or card payment for a reservation.
     */
    public function store(Request $request, Reservation $reservation)
    {
        $request->validate([
            'amount'   => 'required|numeric|min:0.01',
            'provider' => 'required|in:stripe,cash,bank_transfer,pos_terminal',
        ]);

        $amountMinor = (int) round($request->input('amount') * 100);
        $provider    = $request->input('provider');

        if ($provider === 'stripe') {
            $res = $this->stripeAdapter->createPaymentIntent($reservation->property, $amountMinor, $reservation->currency, [
                'reservation_id' => $reservation->id,
            ]);
            $paymentId = $res['payment_intent_id'] ?? ('pi_stripe_' . Str::random(8));
        } else {
            $res = $this->offlineAdapter->createPaymentIntent($reservation->property, $amountMinor, $reservation->currency, [
                'provider' => $provider,
            ]);
            $paymentId = $res['payment_intent_id'] ?? ('off_' . Str::random(8));
        }

        $payment = Payment::create([
            'ulid'                => (string) Str::ulid(),
            'reservation_id'      => $reservation->id,
            'property_id'         => $reservation->property_id,
            'provider'            => $provider,
            'provider_payment_id' => $paymentId,
            'amount_minor'        => $amountMinor,
            'currency'            => $reservation->currency,
            'status'              => 'captured',
            'type'                => 'capture',
            'captured_at'         => now(),
            'processed_by'        => auth()->id(),
        ]);

        // Update reservation balance
        $newBalance = max(0, $reservation->balance_minor - $amountMinor);
        $reservation->update([
            'deposit_minor' => $reservation->deposit_minor + $amountMinor,
            'balance_minor' => $newBalance,
        ]);

        // Auto-generate invoice
        $invoice = $this->invoiceService->generateForReservation($reservation, 'receipt');

        // Trigger payment receipt email
        if ($reservation->primaryGuest?->email) {
            try {
                Mail::to($reservation->primaryGuest->email)->send(new PaymentReceiptMail($reservation, $payment, $invoice));
            } catch (\Exception $e) {
                // Log or ignore mail transport failure
            }
        }

        return redirect()->back()->with('success', 'Payment of ' . number_format($amountMinor / 100, 2) . ' ' . $reservation->currency . ' processed successfully.');
    }

    /**
     * Generate or download PDF invoice.
     */
    public function downloadInvoice(Reservation $reservation)
    {
        $invoice = Invoice::where('reservation_id', $reservation->id)->latest()->first();

        if (!$invoice || !Storage::disk('local')->exists($invoice->pdf_path)) {
            $invoice = $this->invoiceService->generateForReservation($reservation);
        }

        return Storage::disk('local')->download($invoice->pdf_path, $invoice->invoice_number . '.pdf');
    }

    /**
     * Resend booking confirmation email with attached PDF invoice.
     */
    public function resendConfirmationEmail(Reservation $reservation)
    {
        if (!$reservation->primaryGuest?->email) {
            return redirect()->back()->with('error', 'No email address registered for this guest.');
        }

        $invoice = Invoice::where('reservation_id', $reservation->id)->latest()->first();
        if (!$invoice) {
            $invoice = $this->invoiceService->generateForReservation($reservation);
        }

        try {
            Mail::to($reservation->primaryGuest->email)->send(new BookingConfirmationMail($reservation, $invoice));
            return redirect()->back()->with('success', 'Booking confirmation email sent to ' . $reservation->primaryGuest->email);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Email delivery failed: ' . $e->getMessage());
        }
    }
}
