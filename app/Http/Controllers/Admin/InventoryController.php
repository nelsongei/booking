<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditService;
use App\Domain\Inventory\AvailabilityService;
use App\Domain\Inventory\HoldService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\InventoryAdjustment;
use App\Infrastructure\Persistence\InventoryDay;
use App\Infrastructure\Persistence\InventoryHold;
use App\Infrastructure\Persistence\RoomType;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected AvailabilityService $availabilityService;
    protected HoldService $holdService;

    public function __construct(AvailabilityService $availabilityService, HoldService $holdService)
    {
        $this->middleware('auth');
        $this->availabilityService = $availabilityService;
        $this->holdService         = $holdService;
    }

    /**
     * Display the 14-day availability matrix.
     */
    public function matrix(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;

        if (!$property) {
            return redirect()->route('admin.properties.index')
                ->with('error', 'Please select or create a property first.');
        }

        $checkIn  = $request->get('check_in', now()->toDateString());
        $checkOut = $request->get('check_out', now()->addDays(14)->toDateString());

        $availability = $this->availabilityService->checkAvailability($property, $checkIn, $checkOut);
        $roomTypes    = RoomType::where('property_id', $property->id)->where('status', 'active')->get();
        $holds        = InventoryHold::where('property_id', $property->id)->where('status', 'active')->with('roomType')->get();

        return view('admin.inventory.matrix', compact('property', 'availability', 'roomTypes', 'holds', 'checkIn', 'checkOut'));
    }

    /**
     * Adjust inventory parameters for a date range (block rooms, add protected inventory, etc.).
     */
    public function adjust(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'type'         => 'required|in:block,unblock,protect,unprotect,overbooking',
            'quantity'     => 'required|integer|min:1',
            'reason'       => 'nullable|string|max:255',
        ]);

        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $endDate   = \Carbon\Carbon::parse($data['end_date']);

        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dateStr = $d->toDateString();

            $invDay = InventoryDay::firstOrCreate(
                ['property_id' => $property->id, 'room_type_id' => $data['room_type_id'], 'date' => $dateStr],
                ['total' => 10]
            );

            if ($data['type'] === 'block') {
                $invDay->increment('blocked', $data['quantity']);
            } elseif ($data['type'] === 'unblock') {
                $invDay->decrement('blocked', min($invDay->blocked, $data['quantity']));
            } elseif ($data['type'] === 'protect') {
                $invDay->increment('protected', $data['quantity']);
            } elseif ($data['type'] === 'unprotect') {
                $invDay->decrement('protected', min($invDay->protected, $data['quantity']));
            } elseif ($data['type'] === 'overbooking') {
                $invDay->update(['overbooking_allowed' => $data['quantity']]);
            }

            InventoryAdjustment::create([
                'property_id'  => $property->id,
                'room_type_id' => $data['room_type_id'],
                'date'         => $dateStr,
                'type'         => $data['type'],
                'quantity'     => $data['quantity'],
                'reason'       => $data['reason'],
                'created_by'   => auth()->id(),
            ]);
        }

        AuditService::log('inventory.adjusted', 'InventoryDay', null, null, $data, ['property_id' => $property->id]);

        return redirect()->back()->with('success', 'Inventory adjusted successfully.');
    }

    /**
     * Manually release an active hold.
     */
    public function releaseHold(InventoryHold $hold)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property && $hold->property_id === $property->id, 403);

        $this->holdService->releaseHold($hold);

        return redirect()->back()->with('success', "Inventory hold {$hold->ulid} released.");
    }
}
