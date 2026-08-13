<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Organization;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\RoomType;
use App\Infrastructure\Persistence\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicBookingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected Property $property;
    protected RoomType $roomType;
    protected RatePlan $ratePlan;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Organization
        $org = Organization::create([
            'ulid'   => (string) Str::ulid(),
            'name'   => 'Grand Horizon Hotels Group',
            'slug'   => 'grand-horizon-hotels-group',
            'code'   => 'GHHG',
            'status' => 'active',
        ]);

        // 2. Create Property
        $this->property = Property::create([
            'ulid'                   => (string) Str::ulid(),
            'organization_id'        => $org->id,
            'name'                   => 'Grand Horizon Resort & Spa',
            'code'                   => 'GHR',
            'slug'                   => 'grand-horizon-resort',
            'type'                   => 'resort',
            'description'            => 'Luxury beachfront resort and spa.',
            'currency'               => 'USD',
            'timezone'               => 'UTC',
            'locale'                 => 'en',
            'status'                 => 'active',
            'booking_engine_enabled' => true,
            'booking_engine_slug'    => 'grand-horizon-resort',
            'check_in_out_times'     => ['check_in' => '15:00', 'check_out' => '11:00'],
        ]);

        // 3. Create Room Type
        $this->roomType = RoomType::create([
            'ulid'            => (string) Str::ulid(),
            'organization_id' => $org->id,
            'property_id'     => $this->property->id,
            'name'            => 'Ocean Deluxe King Suite',
            'code'            => 'ODKS',
            'max_occupancy'   => 3,
            'base_adults'     => 2,
            'base_children'   => 1,
            'status'          => 'active',
        ]);

        // 4. Create Physical Rooms (3 rooms)
        for ($i = 101; $i <= 103; $i++) {
            Room::create([
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'property_id'     => $this->property->id,
                'room_type_id'    => $this->roomType->id,
                'room_number'     => (string) $i,
                'status'          => 'clean',
            ]);
        }

        // 5. Create Rate Plan
        $this->ratePlan = RatePlan::create([
            'ulid'                   => (string) Str::ulid(),
            'organization_id'        => $org->id,
            'property_id'            => $this->property->id,
            'name'                   => 'Best Available Flex Rate',
            'code'                   => 'BAR-FLEX',
            'type'                   => 'public',
            'pricing_model'          => 'per_room',
            'currency'               => 'USD',
            'default_rate_minor'     => 15000, // $150.00
            'extra_adult_minor'      => 3000,
            'extra_child_minor'      => 1500,
            'is_default'             => true,
            'status'                 => 'active',
        ]);
    }

    public function test_public_guest_can_view_property_booking_page()
    {
        $response = $this->get(route('booking.index', ['slug' => $this->property->slug]));

        $response->assertStatus(200);
        $response->assertSee('Grand Horizon Resort & Spa');
        $response->assertSee('Find Available Rooms');
    }

    public function test_guest_can_search_room_availability_and_get_quotes()
    {
        $checkIn  = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(7)->toDateString();

        $response = $this->get(route('booking.search', [
            'slug'      => $this->property->slug,
            'check_in'  => $checkIn,
            'check_out' => $checkOut,
            'adults'    => 2,
            'children'  => 0,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Ocean Deluxe King Suite');
        $response->assertSee('Best Available Flex Rate');
    }

    public function test_guest_review_step_creates_15_minute_inventory_hold()
    {
        $checkIn  = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(7)->toDateString();

        $response = $this->post(route('booking.review', ['slug' => $this->property->slug]), [
            'check_in'         => $checkIn,
            'check_out'        => $checkOut,
            'adults'           => 2,
            'children'         => 0,
            'room_type_id'     => $this->roomType->id,
            'rate_plan_id'     => $this->ratePlan->id,
            'guest_first_name' => 'Alice',
            'guest_last_name'  => 'Smith',
            'guest_email'      => 'alice.smith@example.com',
            'guest_phone'      => '+15551234567',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Inventory Locked for You');
        $response->assertSee('Alice Smith');
        $this->assertDatabaseHas('inventory_holds', [
            'property_id'  => $this->property->id,
            'room_type_id' => $this->roomType->id,
            'status'       => 'active',
        ]);
    }

    public function test_guest_can_complete_reservation_and_view_confirmation()
    {
        $checkIn  = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(7)->toDateString();

        $response = $this->post(route('booking.confirm', ['slug' => $this->property->slug]), [
            'check_in'         => $checkIn,
            'check_out'        => $checkOut,
            'adults'           => 2,
            'children'         => 0,
            'room_type_id'     => $this->roomType->id,
            'rate_plan_id'     => $this->ratePlan->id,
            'guest_first_name' => 'Robert',
            'guest_last_name'  => 'Johnson',
            'guest_email'      => 'robert.j@example.com',
            'guest_phone'      => '+15559876543',
        ]);

        $response->assertRedirect();
        $reservation = Reservation::where('property_id', $this->property->id)->first();
        $this->assertNotNull($reservation);
        $this->assertEquals('confirmed', $reservation->status);
        $this->assertEquals('direct_web', $reservation->booking_channel);

        // Follow redirect to confirmation view
        $confResponse = $this->get(route('booking.confirmation', [
            'slug'                => $this->property->slug,
            'confirmationNumber' => $reservation->confirmation_number,
        ]));

        $confResponse->assertStatus(200);
        $confResponse->assertSee($reservation->confirmation_number);
        $confResponse->assertSee('Reservation Confirmed!');
    }

    public function test_guest_can_lookup_reservation_in_portal()
    {
        // 1. Create a reservation
        $checkIn  = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(7)->toDateString();

        $this->post(route('booking.confirm', ['slug' => $this->property->slug]), [
            'check_in'         => $checkIn,
            'check_out'        => $checkOut,
            'adults'           => 2,
            'children'         => 0,
            'room_type_id'     => $this->roomType->id,
            'rate_plan_id'     => $this->ratePlan->id,
            'guest_first_name' => 'Sarah',
            'guest_last_name'  => 'Connor',
            'guest_email'      => 'sarah.c@example.com',
            'guest_phone'      => '+15551112222',
        ]);

        $reservation = Reservation::where('property_id', $this->property->id)->first();

        // 2. Search in guest portal
        $response = $this->post(route('booking.portal.search'), [
            'confirmation_number' => $reservation->confirmation_number,
            'email'               => 'sarah.c@example.com',
        ]);

        $response->assertRedirect(route('booking.portal.show', [
            'confirmationNumber' => $reservation->confirmation_number,
        ]));

        // 3. View portal page
        $showResponse = $this->get(route('booking.portal.show', [
            'confirmationNumber' => $reservation->confirmation_number,
        ]));

        $showResponse->assertStatus(200);
        $showResponse->assertSee($reservation->confirmation_number);
        $showResponse->assertSee('Sarah Connor');
    }
}
