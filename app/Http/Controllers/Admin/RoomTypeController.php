<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Amenity;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomTypeController extends Controller
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

        $roomTypes = RoomType::where('property_id', $property->id)
            ->withCount('rooms')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.room_types.index', compact('property', 'roomTypes'));
    }

    public function create()
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $amenities = Amenity::orderBy('category')->orderBy('name')->get()->groupBy('category');

        return view('admin.room_types.create', compact('property', 'amenities'));
    }

    public function store(Request $request)
    {
        $property = app()->bound('current.property') ? app('current.property') : null;
        abort_unless($property, 404, 'No active property.');

        $data = $request->validate([
            'code'            => 'required|string|max:20',
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'bed_type'        => 'required|string|in:king,queen,twin,double,single',
            'base_occupancy'  => 'required|integer|min:1',
            'max_adults'      => 'required|integer|min:1',
            'max_children'    => 'required|integer|min:0',
            'max_occupancy'   => 'required|integer|min:1',
            'size_sqm'        => 'nullable|integer|min:1',
            'view'            => 'nullable|string|max:100',
            'is_accessible'   => 'boolean',
            'smoking_allowed' => 'boolean',
            'status'          => 'required|in:active,inactive',
            'amenities'       => 'nullable|array',
            'amenities.*'     => 'exists:amenities,id',
        ]);

        $exists = RoomType::where('property_id', $property->id)
            ->where('code', strtoupper($data['code']))
            ->exists();

        abort_if($exists, 422, 'A room type with this code already exists for this property.');

        $data['ulid']            = (string) Str::ulid();
        $data['organization_id'] = $property->organization_id;
        $data['property_id']     = $property->id;
        $data['code']            = strtoupper($data['code']);
        $data['is_accessible']   = $request->boolean('is_accessible');
        $data['smoking_allowed'] = $request->boolean('smoking_allowed');

        $amenityIds = $data['amenities'] ?? [];
        unset($data['amenities']);

        $roomType = RoomType::create($data);

        if (!empty($amenityIds)) {
            $roomType->amenities()->sync($amenityIds);
        }

        AuditService::log('room_type.created', 'RoomType', $roomType->ulid, null, $roomType->toArray(), [
            'property_id' => $property->id,
        ]);

        return redirect()->route('admin.room-types.show', $roomType)
            ->with('success', "Room Type '{$roomType->name}' created successfully.");
    }

    public function show(RoomType $roomType)
    {
        $this->authorizeAccess($roomType);
        $roomType->load(['amenities', 'images', 'rooms']);
        return view('admin.room_types.show', compact('roomType'));
    }

    public function edit(RoomType $roomType)
    {
        $this->authorizeAccess($roomType);
        $amenities = Amenity::orderBy('category')->orderBy('name')->get()->groupBy('category');
        return view('admin.room_types.edit', compact('roomType', 'amenities'));
    }

    public function update(Request $request, RoomType $roomType)
    {
        $this->authorizeAccess($roomType);

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'bed_type'        => 'required|string|in:king,queen,twin,double,single',
            'base_occupancy'  => 'required|integer|min:1',
            'max_adults'      => 'required|integer|min:1',
            'max_children'    => 'required|integer|min:0',
            'max_occupancy'   => 'required|integer|min:1',
            'size_sqm'        => 'nullable|integer|min:1',
            'view'            => 'nullable|string|max:100',
            'is_accessible'   => 'boolean',
            'smoking_allowed' => 'boolean',
            'status'          => 'required|in:active,inactive',
            'amenities'       => 'nullable|array',
            'amenities.*'     => 'exists:amenities,id',
        ]);

        $before = $roomType->toArray();

        $data['is_accessible']   = $request->boolean('is_accessible');
        $data['smoking_allowed'] = $request->boolean('smoking_allowed');

        $amenityIds = $data['amenities'] ?? [];
        unset($data['amenities']);

        $roomType->update($data);
        $roomType->amenities()->sync($amenityIds);

        AuditService::log('room_type.updated', 'RoomType', $roomType->ulid, $before, $roomType->fresh()->toArray(), [
            'property_id' => $roomType->property_id,
        ]);

        return redirect()->route('admin.room-types.show', $roomType)
            ->with('success', 'Room type updated successfully.');
    }

    private function authorizeAccess(RoomType $roomType): void
    {
        $user = auth()->user();
        abort_unless($user->is_platform_admin || $user->organization_id === $roomType->organization_id, 403);
    }
}
