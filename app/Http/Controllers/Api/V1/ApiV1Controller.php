<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Inventory\AvailabilityService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Property;
use Illuminate\Http\Request;

class ApiV1Controller extends Controller
{
    protected AvailabilityService $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function properties(Request $request)
    {
        $properties = Property::where('status', 'active')
            ->select(['id', 'ulid', 'name', 'code', 'slug', 'currency', 'timezone'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $properties,
        ]);
    }

    public function availability(Request $request, Property $property)
    {
        $request->validate([
            'check_in'  => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        $result = $this->availabilityService->checkAvailability(
            $property,
            $request->check_in,
            $request->check_out,
            $request->room_type_id ? (int)$request->room_type_id : null
        );

        return response()->json([
            'status' => 'success',
            'data'   => $result,
        ]);
    }
}
