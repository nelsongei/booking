<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\CancellationPolicy;
use App\Infrastructure\Persistence\DepositPolicy;
use App\Infrastructure\Persistence\MealPlan;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\RateDay;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RatePlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;

        if (!$property) {
            return redirect()->route('admin.properties.index')
                ->with('error', 'Please select or create a property first.');
        }

        $ratePlans = RatePlan::where('property_id', $property->id)
            ->with(['mealPlan', 'cancellationPolicy', 'depositPolicy', 'roomTypes'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.rate_plans.index', compact('property', 'ratePlans'));
    }

    public function create()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $mealPlans   = MealPlan::orderBy('code')->get();
        $cancelPols  = CancellationPolicy::where('organization_id', $property->organization_id)->get();
        $depositPols = DepositPolicy::where('organization_id', $property->organization_id)->get();
        $roomTypes   = RoomType::where('property_id', $property->id)->where('status', 'active')->get();

        return view('admin.rate_plans.create', compact('property', 'mealPlans', 'cancelPols', 'depositPols', 'roomTypes'));
    }

    public function store(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'code'                   => 'required|string|max:20',
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string',
            'currency'               => 'required|string|size:3',
            'meal_plan_id'           => 'nullable|exists:meal_plans,id',
            'cancellation_policy_id' => 'nullable|exists:cancellation_policies,id',
            'deposit_policy_id'      => 'nullable|exists:deposit_policies,id',
            'is_public'              => 'boolean',
            'is_refundable'          => 'boolean',
            'breakfast_included'     => 'boolean',
            'min_advance_days'       => 'nullable|integer|min:0',
            'max_advance_days'       => 'nullable|integer|min:0',
            'is_active'              => 'boolean',
            'room_type_ids'          => 'nullable|array',
            'room_type_ids.*'        => 'exists:room_types,id',
        ]);

        $exists = RatePlan::where('property_id', $property->id)
            ->where('code', strtoupper($data['code']))
            ->exists();

        abort_if($exists, 422, "Rate plan code '{$data['code']}' already exists.");

        $data['ulid']               = (string) Str::ulid();
        $data['organization_id']    = $property->organization_id;
        $data['property_id']        = $property->id;
        $data['code']               = strtoupper($data['code']);
        $data['is_public']          = $request->boolean('is_public');
        $data['is_refundable']      = $request->boolean('is_refundable');
        $data['breakfast_included'] = $request->boolean('breakfast_included');
        $data['is_active']          = $request->boolean('is_active', true);

        $roomTypeIds = $data['room_type_ids'] ?? [];
        unset($data['room_type_ids']);

        $ratePlan = RatePlan::create($data);

        if (!empty($roomTypeIds)) {
            $ratePlan->roomTypes()->sync($roomTypeIds);
        }

        AuditService::log('rate_plan.created', 'RatePlan', $ratePlan->ulid, null, $ratePlan->toArray(), [
            'property_id' => $property->id,
        ]);

        return redirect()->route('admin.rate-plans.show', $ratePlan)
            ->with('success', "Rate Plan '{$ratePlan->name}' created successfully.");
    }

    public function show(RatePlan $ratePlan)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property && $ratePlan->property_id === $property->id, 403);

        $ratePlan->load(['mealPlan', 'cancellationPolicy', 'depositPolicy', 'roomTypes']);

        // Load 14 days of rate entries for preview
        $startDate = now()->toDateString();
        $endDate   = now()->addDays(14)->toDateString();

        $rates = RateDay::where('rate_plan_id', $ratePlan->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('room_type_id');

        return view('admin.rate_plans.show', compact('ratePlan', 'rates', 'startDate', 'endDate'));
    }

    public function edit(RatePlan $ratePlan)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property && $ratePlan->property_id === $property->id, 403);

        $mealPlans   = MealPlan::orderBy('code')->get();
        $cancelPols  = CancellationPolicy::where('organization_id', $property->organization_id)->get();
        $depositPols = DepositPolicy::where('organization_id', $property->organization_id)->get();
        $roomTypes   = RoomType::where('property_id', $property->id)->where('status', 'active')->get();

        return view('admin.rate_plans.edit', compact('ratePlan', 'mealPlans', 'cancelPols', 'depositPols', 'roomTypes'));
    }

    public function update(Request $request, RatePlan $ratePlan)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property && $ratePlan->property_id === $property->id, 403);

        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string',
            'currency'               => 'required|string|size:3',
            'meal_plan_id'           => 'nullable|exists:meal_plans,id',
            'cancellation_policy_id' => 'nullable|exists:cancellation_policies,id',
            'deposit_policy_id'      => 'nullable|exists:deposit_policies,id',
            'is_public'              => 'boolean',
            'is_refundable'          => 'boolean',
            'breakfast_included'     => 'boolean',
            'min_advance_days'       => 'nullable|integer|min:0',
            'max_advance_days'       => 'nullable|integer|min:0',
            'is_active'              => 'boolean',
            'room_type_ids'          => 'nullable|array',
            'room_type_ids.*'        => 'exists:room_types,id',
        ]);

        $before = $ratePlan->toArray();

        $data['is_public']          = $request->boolean('is_public');
        $data['is_refundable']      = $request->boolean('is_refundable');
        $data['breakfast_included'] = $request->boolean('breakfast_included');
        $data['is_active']          = $request->boolean('is_active');

        $roomTypeIds = $data['room_type_ids'] ?? [];
        unset($data['room_type_ids']);

        $ratePlan->update($data);
        $ratePlan->roomTypes()->sync($roomTypeIds);

        AuditService::log('rate_plan.updated', 'RatePlan', $ratePlan->ulid, $before, $ratePlan->fresh()->toArray(), [
            'property_id' => $property->id,
        ]);

        return redirect()->route('admin.rate-plans.show', $ratePlan)
            ->with('success', "Rate Plan '{$ratePlan->name}' updated successfully.");
    }

    /**
     * Batch save daily rates (AJAX or form)
     */
    public function saveDailyRates(Request $request, RatePlan $ratePlan)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property && $ratePlan->property_id === $property->id, 403);

        $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'amount'       => 'required|numeric|min:0',
        ]);

        $amountMinor = (int) round($request->amount * 100);
        $startDate   = \Carbon\Carbon::parse($request->start_date);
        $endDate     = \Carbon\Carbon::parse($request->end_date);

        $count = 0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            RateDay::updateOrCreate(
                [
                    'property_id'  => $property->id,
                    'rate_plan_id' => $ratePlan->id,
                    'room_type_id' => $request->room_type_id,
                    'date'         => $date->toDateString(),
                ],
                [
                    'amount_minor' => $amountMinor,
                    'currency'     => $ratePlan->currency,
                ]
            );
            $count++;
        }

        return redirect()->back()->with('success', "Updated rates for {$count} day(s).");
    }
}
