<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Integrations\ChannelManagerService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\IntegrationConnection;
use App\Infrastructure\Persistence\Property;
use Illuminate\Http\Request;

class ChannelManagerController extends Controller
{
    protected ChannelManagerService $channelService;

    public function __construct(ChannelManagerService $channelService)
    {
        $this->middleware('auth');
        $this->channelService = $channelService;
    }

    protected function resolveCurrentProperty(): ?Property
    {
        return app()->bound('current.property') ? app('current.property') : Property::first();
    }

    /**
     * Channel Manager Dashboard View.
     */
    public function index(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $supportedProviders = [
            'booking_com' => ['name' => 'Booking.com', 'icon' => 'bi-building-gear', 'category' => 'OTA'],
            'airbnb'      => ['name' => 'Airbnb', 'icon' => 'bi-house-heart', 'category' => 'Vacation Rental'],
            'expedia'     => ['name' => 'Expedia Partner', 'icon' => 'bi-globe-americas', 'category' => 'OTA'],
            'agoda'       => ['name' => 'Agoda YCS', 'icon' => 'bi-building-fill-check', 'category' => 'OTA'],
        ];

        $connections = $property ? IntegrationConnection::where('property_id', $property->id)->get()->keyBy('provider') : collect();

        return view('admin.modules.channel_manager', compact(
            'property', 'supportedProviders', 'connections'
        ));
    }

    /**
     * Update/Configure Channel Connection.
     */
    public function updateConnection(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        $request->validate([
            'provider' => 'required|string',
            'status'   => 'required|string|in:active,inactive',
            'api_key'  => 'nullable|string',
        ]);

        try {
            IntegrationConnection::updateOrCreate(
                [
                    'property_id' => $property->id,
                    'provider'    => $request->input('provider'),
                ],
                [
                    'organization_id'       => $property->organization_id,
                    'ulid'                  => (string) \Illuminate\Support\Str::ulid(),
                    'status'                => $request->input('status'),
                    'credentials_encrypted' => ['api_key' => $request->input('api_key')],
                ]
            );

            return redirect()->back()->with('success', 'Channel connection updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Connection update failed: ' . $e->getMessage());
        }
    }

    /**
     * Trigger outbound inventory sync.
     */
    public function triggerSync(Request $request)
    {
        $property = $this->resolveCurrentProperty();

        try {
            $result = $this->channelService->syncOutboundInventory($property);
            return redirect()->back()->with('success', "Outbound sync completed. Synced {$result['channels_synced']} channel connection(s).");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Inventory sync failed: ' . $e->getMessage());
        }
    }
}
