<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reservation\CancelReservationAction;
use App\Domain\Reservation\CreateReservationAction;
use App\Domain\Reservation\ReservationStateMachine;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\RoomType;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected CreateReservationAction $createAction;
    protected CancelReservationAction $cancelAction;
    protected ReservationStateMachine $stateMachine;

    public function __construct(
        CreateReservationAction $createAction,
        CancelReservationAction $cancelAction,
        ReservationStateMachine $stateMachine
    ) {
        $this->middleware('auth');
        $this->createAction = $createAction;
        $this->cancelAction = $cancelAction;
        $this->stateMachine = $stateMachine;
    }

    public function index(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;

        if (!$property) {
            return redirect()->route('admin.properties.index')
                ->with('error', 'Please select or create a property first.');
        }

        $query = Reservation::where('property_id', $property->id)
            ->with(['primaryGuest', 'rooms.roomType', 'ratePlan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('confirmation_number', 'like', "%{$s}%")
                  ->orWhereHas('primaryGuest', function ($gq) use ($s) {
                      $gq->where('first_name', 'like', "%{$s}%")
                        ->orWhere('last_name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reservations.index', compact('property', 'reservations'));
    }

    public function create()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $roomTypes = RoomType::where('property_id', $property->id)->where('status', 'active')->get();
        $ratePlans = RatePlan::where('property_id', $property->id)->where('is_active', true)->get();

        return view('admin.reservations.create', compact('property', 'roomTypes', 'ratePlans'));
    }

    public function store(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'room_type_id'     => 'required|exists:room_types,id',
            'rate_plan_id'     => 'required|exists:rate_plans,id',
            'check_in'         => 'required|date|after_or_equal:today',
            'check_out'        => 'required|date|after:check_in',
            'adults'           => 'required|integer|min:1',
            'children'         => 'required|integer|min:0',
            'guest_first_name' => 'required|string|max:255',
            'guest_last_name'  => 'required|string|max:255',
            'guest_email'      => 'required|email|max:255',
            'guest_phone'      => 'nullable|string|max:50',
            'special_requests' => 'nullable|string',
        ]);

        $data['property_id'] = $property->id;

        try {
            $reservation = $this->createAction->execute($data);
            return redirect()->route('admin.reservations.show', $reservation)
                ->with('success', "Reservation {$reservation->confirmation_number} created successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Reservation $reservation)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property && $reservation->property_id === $property->id, 403);

        $reservation->load([
            'primaryGuest', 'ratePlan', 'rooms.roomType',
            'rooms.nights', 'statusHistory.changedBy', 'notes.user'
        ]);

        return view('admin.reservations.show', compact('reservation'));
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property && $reservation->property_id === $property->id, 403);

        $data = $request->validate([
            'status' => 'required|string',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            if ($data['status'] === 'cancelled') {
                $this->cancelAction->execute($reservation, $data['reason'] ?? 'Staff cancellation');
            } else {
                $this->stateMachine->transition($reservation, $data['status'], $data['reason'] ?? null);
            }

            return redirect()->back()->with('success', "Reservation status updated to " . ucfirst(str_replace('_', ' ', $data['status'])));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
