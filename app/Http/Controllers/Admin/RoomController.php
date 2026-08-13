<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Building;
use App\Infrastructure\Persistence\Floor;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomController extends Controller
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

        $query = Room::where('property_id', $property->id)->with(['roomType', 'building', 'floor']);

        if ($request->has('room_type_id') && $request->room_type_id) {
            $query->where('room_type_id', $request->room_type_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $rooms     = $query->orderBy('room_number')->paginate(25);
        $roomTypes = RoomType::where('property_id', $property->id)->orderBy('name')->get();

        return view('admin.rooms.index', compact('property', 'rooms', 'roomTypes'));
    }

    public function create()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $roomTypes = RoomType::where('property_id', $property->id)->where('status', 'active')->orderBy('name')->get();
        $buildings = Building::where('property_id', $property->id)->orderBy('name')->get();
        $floors    = Floor::where('property_id', $property->id)->orderBy('level')->get();

        return view('admin.rooms.create', compact('property', 'roomTypes', 'buildings', 'floors'));
    }

    public function store(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'building_id'  => 'nullable|exists:buildings,id',
            'floor_id'     => 'nullable|exists:floors,id',
            'room_number'  => 'required|string|max:20',
            'name'         => 'nullable|string|max:255',
            'status'       => 'required|in:clean,dirty,inspected,out_of_order,out_of_service',
            'is_smoking'   => 'boolean',
            'notes'        => 'nullable|string',
        ]);

        $exists = Room::where('property_id', $property->id)
            ->where('room_number', $data['room_number'])
            ->exists();

        abort_if($exists, 422, "Room number '{$data['room_number']}' already exists in this property.");

        $data['ulid']        = (string) Str::ulid();
        $data['property_id'] = $property->id;
        $data['is_smoking']  = $request->boolean('is_smoking');

        $room = Room::create($data);

        AuditService::log('room.created', 'Room', $room->ulid, null, $room->toArray(), [
            'property_id' => $property->id,
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room '{$room->room_number}' created successfully.");
    }

    public function edit(Room $room)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property && $room->property_id === $property->id, 403);

        $roomTypes = RoomType::where('property_id', $property->id)->orderBy('name')->get();
        $buildings = Building::where('property_id', $property->id)->orderBy('name')->get();
        $floors    = Floor::where('property_id', $property->id)->orderBy('level')->get();

        return view('admin.rooms.edit', compact('room', 'roomTypes', 'buildings', 'floors'));
    }

    public function update(Request $request, Room $room)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property && $room->property_id === $property->id, 403);

        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'building_id'  => 'nullable|exists:buildings,id',
            'floor_id'     => 'nullable|exists:floors,id',
            'name'         => 'nullable|string|max:255',
            'status'       => 'required|in:clean,dirty,inspected,out_of_order,out_of_service',
            'is_smoking'   => 'boolean',
            'notes'        => 'nullable|string',
        ]);

        $before = $room->toArray();
        $data['is_smoking'] = $request->boolean('is_smoking');

        $room->update($data);

        AuditService::log('room.updated', 'Room', $room->ulid, $before, $room->fresh()->toArray(), [
            'property_id' => $property->id,
        ]);

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room '{$room->room_number}' updated successfully.");
    }
}
