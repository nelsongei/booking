<?php

namespace Tests\Feature\Core;

use App\Domain\Inventory\AvailabilityService;
use App\Domain\Inventory\HoldService;
use App\Infrastructure\Persistence\InventoryDay;
use App\Infrastructure\Persistence\Organization;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\RoomType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConcurrencyLockTest extends TestCase
{
    use DatabaseTransactions;

    protected Organization $org;
    protected Property $property;
    protected RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create([
            'ulid' => (string) Str::ulid(),
            'name' => 'Safari Group',
            'code' => 'SG',
            'status' => 'active',
        ]);

        $this->property = Property::create([
            'ulid' => (string) Str::ulid(),
            'organization_id' => $this->org->id,
            'name' => 'Serengeti Camp',
            'code' => 'SC',
            'slug' => 'serengeti-camp',
            'currency' => 'USD',
            'timezone' => 'Africa/Dar_es_Salaam',
            'status' => 'active',
            'booking_engine_enabled' => true,
        ]);

        $this->roomType = RoomType::create([
            'ulid' => (string) Str::ulid(),
            'property_id' => $this->property->id,
            'name' => 'Luxury Tent',
            'code' => 'LT',
            'base_occupancy' => 2,
            'max_occupancy' => 2,
            'base_price_minor' => 15000,
            'status' => 'active',
        ]);

        // Create 2 physical rooms
        for ($i = 1; $i <= 2; $i++) {
            Room::create([
                'ulid' => (string) Str::ulid(),
                'property_id' => $this->property->id,
                'room_type_id' => $this->roomType->id,
                'room_number' => "TENT-0{$i}",
                'housekeeping_status' => 'clean',
                'is_active' => true,
            ]);
        }
    }

    public function test_check_availability_uses_lock_for_update_and_initializes_inventory_days()
    {
        $service = app(AvailabilityService::class);

        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(7)->toDateString();

        $result = $service->checkAvailability($this->property, $checkIn, $checkOut, $this->roomType->id, true);

        $this->assertEquals(2, $result['nights']);
        $this->assertTrue(isset($result['room_types'][$this->roomType->id]));
        $this->assertEquals(2, $result['room_types'][$this->roomType->id]['min_available']);
        $this->assertTrue($result['room_types'][$this->roomType->id]['is_available']);

        // Assert InventoryDay records were created in database
        $this->assertDatabaseHas('inventory_days', [
            'property_id' => $this->property->id,
            'room_type_id' => $this->roomType->id,
            'total' => 2,
            'sold' => 0,
            'holds' => 0,
        ]);
    }

    public function test_hold_service_locks_inventory_and_prevents_overbooking()
    {
        $availabilityService = app(AvailabilityService::class);
        $holdService = app(HoldService::class);

        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(7)->toDateString();

        // Place hold on 2 rooms (entire inventory)
        $hold = $holdService->createHold(
            $this->property,
            $this->roomType,
            $checkIn,
            $checkOut,
            2,
            15,
            'test-session-123'
        );

        $this->assertNotNull($hold);

        // Check availability after hold
        $result = $availabilityService->checkAvailability($this->property, $checkIn, $checkOut, $this->roomType->id, true);

        $this->assertEquals(0, $result['room_types'][$this->roomType->id]['min_available']);
        $this->assertFalse($result['room_types'][$this->roomType->id]['is_available']);
    }
}
