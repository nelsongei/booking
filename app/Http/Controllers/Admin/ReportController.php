<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reporting\KPIAnalyticsService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    protected KPIAnalyticsService $analyticsService;

    public function __construct(KPIAnalyticsService $analyticsService)
    {
        $this->middleware('auth');
        $this->analyticsService = $analyticsService;
    }

    protected function resolveCurrentProperty(): ?Property
    {
        return app()->bound('current.property') ? app('current.property') : Property::first();
    }

    /**
     * Reporting & Analytics Dashboard View.
     */
    public function index(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $startDate = $request->input('start_date', now()->subDays(29)->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());

        if (!$property) {
            return view('admin.modules.reports', [
                'property'   => null,
                'startDate'  => $startDate,
                'endDate'    => $endDate,
                'metrics'    => [],
                'timeSeries' => [],
                'channels'   => [],
            ]);
        }

        $metrics    = $this->analyticsService->getMetricsOverview($property, $startDate, $endDate);
        $timeSeries = $this->analyticsService->getTimeSeriesTrends($property, $startDate, $endDate);
        $channels   = $this->analyticsService->getBookingSourceDistribution($property, $startDate, $endDate);

        return view('admin.modules.reports', compact(
            'property', 'startDate', 'endDate', 'metrics', 'timeSeries', 'channels'
        ));
    }

    /**
     * Export daily metrics and transactions as CSV stream.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $property  = $this->resolveCurrentProperty();
        $startDate = $request->input('start_date', now()->subDays(29)->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());

        $fileName  = 'pms_report_' . $startDate . '_to_' . $endDate . '.csv';

        $reservations = $property ? Reservation::where('property_id', $property->id)
            ->whereBetween('check_in', [$startDate, $endDate])
            ->with(['primaryGuest', 'ratePlan', 'bookingSource'])
            ->get() : collect();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($reservations, $property, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            // CSV Header Row
            fputcsv($file, [
                'Confirmation #', 'Guest Name', 'Check In', 'Check Out',
                'Nights', 'Status', 'Rate Plan', 'Channel', 'Total (' . ($property?->currency ?: 'USD') . ')',
            ]);

            foreach ($reservations as $res) {
                fputcsv($file, [
                    $res->confirmation_number,
                    $res->primaryGuest?->fullName ?: 'Guest',
                    $res->check_in->toDateString(),
                    $res->check_out->toDateString(),
                    $res->nights,
                    ucfirst($res->status),
                    $res->ratePlan?->name ?: 'Standard',
                    $res->bookingSource?->name ?: 'Direct',
                    number_format($res->total_minor / 100, 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export PDF Managerial Summary Report via DomPDF.
     */
    public function exportPdf(Request $request)
    {
        $property  = $this->resolveCurrentProperty();
        $startDate = $request->input('start_date', now()->subDays(29)->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());

        if (!$property) {
            return redirect()->back()->with('error', 'No active property selected for PDF export.');
        }

        $metrics  = $this->analyticsService->getMetricsOverview($property, $startDate, $endDate);
        $channels = $this->analyticsService->getBookingSourceDistribution($property, $startDate, $endDate);

        $pdf = Pdf::loadView('pdf.managerial_report', compact('property', 'startDate', 'endDate', 'metrics', 'channels'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Managerial_Report_' . $property->code . '_' . $startDate . '.pdf');
    }
}
