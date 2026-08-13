<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Stays\StayManagementService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\Stay;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FrontDeskController extends Controller
{
    protected StayManagementService $stayService;

    public function __construct(StayManagementService $stayService)
    {
        $this->middleware('auth');
        $this->stayService = $stayService;
    }

    protected function resolveCurrentProperty(): ?Property
    {
        return app()->bound('current.property') ? app('current.property') : Property::first();
    }

    /**
     * Interactive 14-Day Tape Chart Grid View.
     */
    public function tapeChart(Request $request)
    {
        $property  = $this->resolveCurrentProperty();
        $startDate = $request->has('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfDay();
        $daysCount = 14;

        $dates = [];
        for ($i = 0; $i < $daysCount; $i++) {
            $dates[] = $startDate->copy()->addDays($i);
        }

        $rooms = $property ? Room::where('property_id', $property->id)
            ->with(['roomType', 'assignments.stay.reservation.primaryGuest'])
            ->get() : collect();

        // Get all active stays for this property
        $stays = $property ? Stay::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where('arrival_date', '<=', $startDate->copy()->addDays($daysCount)->toDateString())
            ->where('departure_date', '>=', $startDate->toDateString())
            ->with(['reservation.primaryGuest', 'room', 'roomType'])
            ->get() : collect();

        // Get all active reservations (confirmed, held, checked_in)
        $reservations = $property ? Reservation::where('property_id', $property->id)
            ->whereIn('status', ['confirmed', 'held', 'checked_in'])
            ->where('check_in', '<=', $startDate->copy()->addDays($daysCount)->toDateString())
            ->where('check_out', '>=', $startDate->toDateString())
            ->with(['primaryGuest', 'rooms.roomType', 'stays.room'])
            ->get() : collect();

        // Build Tape Chart Matrix: room_id => dateStr => cell_data
        $matrix = [];
        
        // Group rooms by room_type_id for unassigned reservation mapping
        $roomsByType = [];
        foreach ($rooms as $rm) {
            $roomsByType[$rm->room_type_id][] = $rm;
        }

        $assignedReservationIds = [];

        // 1. Assign explicit Stays to physical rooms
        foreach ($stays as $st) {
            $rmId = $st->room_id;
            if (!$rmId && $st->room_type_id) {
                $possibleRooms = $roomsByType[$st->room_type_id] ?? [];
                if (!empty($possibleRooms)) {
                    $rmId = $possibleRooms[0]->id;
                }
            }
            if ($rmId) {
                $start = Carbon::parse($st->arrival_date);
                $end   = Carbon::parse($st->departure_date);
                for ($d = $start->copy(); $d->lt($end); $d->addDay()) {
                    $dStr = $d->toDateString();
                    $matrix[$rmId][$dStr] = [
                        'type'         => 'stay',
                        'status'       => $st->status,
                        'reservation'  => $st->reservation,
                        'guest'        => $st->reservation?->primaryGuest,
                        'confirmation' => $st->reservation?->confirmation_number,
                    ];
                }
                if ($st->reservation_id) {
                    $assignedReservationIds[] = $st->reservation_id;
                }
            }
        }

        // 2. Map Confirmed / Held Reservations to physical rooms of matching room_type
        foreach ($reservations as $res) {
            if (in_array($res->id, $assignedReservationIds)) {
                continue;
            }

            foreach ($res->rooms as $resRoom) {
                $candidateRooms = $roomsByType[$resRoom->room_type_id] ?? [];
                $resStart = Carbon::parse($res->check_in);
                $resEnd   = Carbon::parse($res->check_out);

                // Find candidate room that is free for these dates
                $assignedRoom = null;
                foreach ($candidateRooms as $candidate) {
                    $isFree = true;
                    for ($d = $resStart->copy(); $d->lt($resEnd); $d->addDay()) {
                        if (isset($matrix[$candidate->id][$d->toDateString()])) {
                            $isFree = false;
                            break;
                        }
                    }
                    if ($isFree) {
                        $assignedRoom = $candidate;
                        break;
                    }
                }

                // Fallback to first room of type if all occupied
                if (!$assignedRoom && !empty($candidateRooms)) {
                    $assignedRoom = $candidateRooms[0];
                }

                if ($assignedRoom) {
                    for ($d = $resStart->copy(); $d->lt($resEnd); $d->addDay()) {
                        $dStr = $d->toDateString();
                        if (!isset($matrix[$assignedRoom->id][$dStr])) {
                            $matrix[$assignedRoom->id][$dStr] = [
                                'type'         => 'reservation',
                                'status'       => $res->status,
                                'reservation'  => $res,
                                'guest'        => $res->primaryGuest,
                                'confirmation' => $res->confirmation_number,
                            ];
                        }
                    }
                }
            }
        }

        return view('admin.front_desk.tape_chart', compact(
            'property', 'startDate', 'dates', 'rooms', 'reservations', 'matrix'
        ));
    }

    /**
     * Expected Arrivals Roster for Selected Date.
     */
    public function arrivals(Request $request)
    {
        $property   = $this->resolveCurrentProperty();
        $targetDate = $request->get('date', now()->toDateString());

        $arrivals = $property ? Reservation::where('property_id', $property->id)
            ->where('check_in', $targetDate)
            ->whereIn('status', ['confirmed', 'held'])
            ->with(['primaryGuest', 'rooms.roomType', 'stays.room'])
            ->get() : collect();

        $availableRooms = $property ? Room::where('property_id', $property->id)
            ->whereIn('status', ['clean', 'inspected', 'vacant'])
            ->with('roomType')
            ->get() : collect();

        return view('admin.front_desk.arrivals', compact('property', 'targetDate', 'arrivals', 'availableRooms'));
    }

    /**
     * Expected Departures Roster for Selected Date.
     */
    public function departures(Request $request)
    {
        $property   = $this->resolveCurrentProperty();
        $targetDate = $request->get('date', now()->toDateString());

        $departures = $property ? Stay::where('property_id', $property->id)
            ->where('departure_date', $targetDate)
            ->where('status', 'checked_in')
            ->with(['reservation.primaryGuest', 'room.roomType'])
            ->get() : collect();

        return view('admin.front_desk.departures', compact('property', 'targetDate', 'departures'));
    }

    /**
     * Currently Checked-In Guests Roster.
     */
    public function inHouse(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $inHouseStays = $property ? Stay::where('property_id', $property->id)
            ->where('status', 'checked_in')
            ->with(['reservation.primaryGuest', 'room.roomType'])
            ->get() : collect();

        $availableRooms = $property ? Room::where('property_id', $property->id)
            ->whereIn('status', ['clean', 'inspected', 'vacant'])
            ->with('roomType')
            ->get() : collect();

        return view('admin.front_desk.in_house', compact('property', 'inHouseStays', 'availableRooms'));
    }

    /**
     * Execute Check-In action.
     */
    public function checkIn(Request $request, Reservation $reservation)
    {
        $request->validate([
            'room_id'   => 'required|exists:rooms,id',
            'id_type'   => 'nullable|string',
            'id_number' => 'nullable|string',
            'notes'     => 'nullable|string',
        ]);

        $room = Room::findOrFail($request->input('room_id'));

        try {
            $stay = $this->stayService->executeCheckIn($reservation, $room, [
                'id_type'   => $request->input('id_type', 'passport'),
                'id_number' => $request->input('id_number'),
                'notes'     => $request->input('notes'),
            ]);

            return redirect()->back()->with('success', 'Guest ' . ($reservation->primaryGuest?->fullName ?: 'Guest') . ' checked in to Room ' . $room->room_number . ' successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Check-In failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute Check-Out action.
     */
    public function checkOut(Request $request, Stay $stay)
    {
        try {
            $this->stayService->executeCheckOut($stay);
            return redirect()->back()->with('success', 'Check-out completed successfully. Room ' . ($stay->room?->room_number ?: '') . ' marked as dirty.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Check-Out failed: ' . $e->getMessage());
        }
    }

    /**
     * Execute Room Move action.
     */
    public function moveRoom(Request $request, Stay $stay)
    {
        $request->validate([
            'new_room_id' => 'required|exists:rooms,id',
            'reason'      => 'nullable|string',
        ]);

        $newRoom = Room::findOrFail($request->input('new_room_id'));

        try {
            $this->stayService->executeRoomMove($stay, $newRoom, $request->input('reason', 'Room move request'));
            return redirect()->back()->with('success', 'Guest successfully moved to Room ' . $newRoom->room_number . '.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Room move failed: ' . $e->getMessage());
        }
    }

    /**
     * Mark Reservation as No-Show.
     */
    public function markNoShow(Request $request, Reservation $reservation)
    {
        try {
            $this->stayService->executeNoShow($reservation);
            return redirect()->back()->with('success', 'Reservation ' . $reservation->confirmation_number . ' marked as No-Show.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No-Show update failed: ' . $e->getMessage());
        }
    }
}
