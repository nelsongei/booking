<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\AuditService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Organization;
use App\Infrastructure\Persistence\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DateTimeZone;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->is_platform_admin) {
            $properties = Property::with('organization')->orderBy('name')->paginate(20);
        } else {
            $properties = Property::where('organization_id', $user->organization_id)
                ->orderBy('name')
                ->paginate(20);
        }

        return view('admin.properties.index', compact('properties'));
    }

    public function create()
    {
        $user          = auth()->user();
        $organizations = $user->is_platform_admin
            ? Organization::orderBy('name')->get()
            : Organization::where('id', $user->organization_id)->get();

        $timezones = DateTimeZone::listIdentifiers();
        $currencies = $this->commonCurrencies();

        return view('admin.properties.create', compact('organizations', 'timezones', 'currencies'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'organization_id'       => 'required|exists:organizations,id',
            'name'                  => 'required|string|max:255',
            'code'                  => 'required|string|max:20',
            'type'                  => 'required|in:hotel,resort,hostel,apartment,villa',
            'description'           => 'nullable|string',
            'address_line1'         => 'nullable|string|max:255',
            'city'                  => 'nullable|string|max:100',
            'country'               => 'nullable|string|size:2',
            'currency'              => 'required|string|size:3',
            'timezone'              => 'required|string|max:100',
            'locale'                => 'required|string|max:10',
            'star_rating'           => 'nullable|integer|min:1|max:5',
            'email'                 => 'nullable|email',
            'phone'                 => 'nullable|string|max:50',
            'check_in_time'         => 'required|string',
            'check_out_time'        => 'required|string',
            'booking_engine_enabled' => 'boolean',
        ]);

        // Org access check
        if (!$user->is_platform_admin) {
            abort_unless($data['organization_id'] == $user->organization_id, 403);
        }

        // Check unique code within org
        $exists = Property::where('organization_id', $data['organization_id'])
            ->where('code', strtoupper($data['code']))
            ->exists();

        abort_if($exists, 422, 'A property with this code already exists in this organization.');

        $data['ulid']                = (string) Str::ulid();
        $data['slug']                = Str::slug($data['name']);
        $data['code']                = strtoupper($data['code']);
        $data['booking_engine_slug'] = Str::slug($data['name']);
        $data['check_in_out_times']  = [
            'check_in'  => $data['check_in_time'],
            'check_out' => $data['check_out_time'],
        ];

        unset($data['check_in_time'], $data['check_out_time']);

        $property = Property::create($data);

        AuditService::log('property.created', 'Property', $property->ulid, null, $property->toArray(), [
            'organization_id' => $property->organization_id,
            'property_id'     => $property->id,
        ]);

        return redirect()->route('admin.properties.show', $property)
            ->with('success', "Property '{$property->name}' created successfully.");
    }

    public function show(Property $property)
    {
        $this->authorizePropertyAccess($property);
        $property->load(['organization', 'roomTypes', 'rooms']);
        $roomTypeCount = $property->roomTypes()->count();
        $roomCount     = $property->rooms()->count();
        return view('admin.properties.show', compact('property', 'roomTypeCount', 'roomCount'));
    }

    public function edit(Property $property)
    {
        $this->authorizePropertyAccess($property);
        $timezones  = DateTimeZone::listIdentifiers();
        $currencies = $this->commonCurrencies();
        return view('admin.properties.edit', compact('property', 'timezones', 'currencies'));
    }

    public function update(Request $request, Property $property)
    {
        $this->authorizePropertyAccess($property);

        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'type'                   => 'required|in:hotel,resort,hostel,apartment,villa',
            'description'            => 'nullable|string',
            'address_line1'          => 'nullable|string|max:255',
            'address_line2'          => 'nullable|string|max:255',
            'city'                   => 'nullable|string|max:100',
            'state'                  => 'nullable|string|max:100',
            'postal_code'            => 'nullable|string|max:20',
            'country'                => 'nullable|string|size:2',
            'currency'               => 'required|string|size:3',
            'timezone'               => 'required|string|max:100',
            'locale'                 => 'required|string|max:10',
            'star_rating'            => 'nullable|integer|min:1|max:5',
            'email'                  => 'nullable|email',
            'phone'                  => 'nullable|string|max:50',
            'website'                => 'nullable|url',
            'check_in_time'          => 'required|string',
            'check_out_time'         => 'required|string',
            'booking_engine_enabled' => 'boolean',
            'status'                 => 'required|in:active,inactive,setup',
        ]);

        $before = $property->toArray();

        $data['check_in_out_times'] = [
            'check_in'  => $data['check_in_time'],
            'check_out' => $data['check_out_time'],
        ];
        unset($data['check_in_time'], $data['check_out_time']);

        $property->update($data);

        AuditService::log('property.updated', 'Property', $property->ulid, $before, $property->fresh()->toArray(), [
            'organization_id' => $property->organization_id,
            'property_id'     => $property->id,
        ]);

        return redirect()->route('admin.properties.show', $property)
            ->with('success', 'Property updated successfully.');
    }

    private function authorizePropertyAccess(Property $property): void
    {
        $user = auth()->user();
        abort_unless($user->is_platform_admin || $user->organization_id === $property->organization_id, 403, 'Access denied.');
    }

    private function commonCurrencies(): array
    {
        return [
            'USD' => 'US Dollar (USD)',
            'EUR' => 'Euro (EUR)',
            'GBP' => 'British Pound (GBP)',
            'KES' => 'Kenyan Shilling (KES)',
            'UGX' => 'Ugandan Shilling (UGX)',
            'TZS' => 'Tanzanian Shilling (TZS)',
            'ZAR' => 'South African Rand (ZAR)',
            'NGN' => 'Nigerian Naira (NGN)',
            'GHS' => 'Ghanaian Cedi (GHS)',
            'AED' => 'UAE Dirham (AED)',
            'INR' => 'Indian Rupee (INR)',
            'AUD' => 'Australian Dollar (AUD)',
            'CAD' => 'Canadian Dollar (CAD)',
            'SGD' => 'Singapore Dollar (SGD)',
        ];
    }
}
