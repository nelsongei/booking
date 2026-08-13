<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Housekeeping\NightAuditOrchestrator;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\NightAudit;
use App\Infrastructure\Persistence\Property;
use Illuminate\Http\Request;

class NightAuditController extends Controller
{
    protected NightAuditOrchestrator $orchestrator;

    public function __construct(NightAuditOrchestrator $orchestrator)
    {
        $this->middleware('auth');
        $this->orchestrator = $orchestrator;
    }

    protected function resolveCurrentProperty(): ?Property
    {
        return app()->bound('current.property') ? app('current.property') : Property::first();
    }

    /**
     * Night Audit Dashboard & Runbook View.
     */
    public function index(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        if (!$property) {
            return view('admin.modules.night_audit', [
                'property'     => null,
                'businessDate' => now()->toDateString(),
                'validation'   => [],
                'activeAudit'  => null,
                'recentAudits' => collect(),
            ]);
        }

        $businessDate = $this->orchestrator->getBusinessDate($property);
        $validation   = $this->orchestrator->validatePreConditions($property, $businessDate);

        $activeAudit = NightAudit::where('property_id', $property->id)
            ->where('business_date', $businessDate)
            ->first();

        $recentAudits = NightAudit::where('property_id', $property->id)
            ->with(['startedBy', 'completedBy'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.modules.night_audit', compact(
            'property', 'businessDate', 'validation', 'activeAudit', 'recentAudits'
        ));
    }

    /**
     * Trigger Night Audit step runner (AJAX-compatible).
     */
    public function run(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        if (!$property) {
            return response()->json(['success' => false, 'message' => 'No active property selected.'], 400);
        }

        try {
            $audit = $this->orchestrator->executeAudit($property, auth()->user());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'status'  => $audit->status,
                    'audit'   => $audit,
                    'message' => $audit->status === 'completed' 
                        ? 'Night Audit completed successfully. Business date rolled to ' . $this->orchestrator->getBusinessDate($property)
                        : 'Audit in progress...',
                ]);
            }

            return redirect()->back()->with('success', 'Night Audit step processed successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Night Audit failed: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Night Audit execution failed: ' . $e->getMessage());
        }
    }

    /**
     * Reset a failed audit for retry.
     */
    public function reset(Request $request, NightAudit $audit)
    {
        try {
            $audit->update([
                'status'        => 'pending',
                'failure_notes' => null,
            ]);

            return redirect()->back()->with('success', 'Night Audit reset for retry.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reset audit: ' . $e->getMessage());
        }
    }
}
