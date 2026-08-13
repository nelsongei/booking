<?php

namespace Tests\Feature;

use App\Domain\Stays\StayManagementService;
use App\Infrastructure\Persistence\GuestProfile;
use App\Infrastructure\Persistence\Organization;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\RoomType;
use App\Infrastructure\Persistence\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FrontDeskAndStayTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Property $property;
    protected Room $room1;
    protected Room $room2;
    protected Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $org = Organization::create([
            'ulid' => (string) Str::ulid(),
            'name' => 'Sapphire Bay Hotels',
            'code' => 'SBH',
            'status' => 'active',
        ]);

        $this->property = Property::create([
            'ulid'                   => (string) Str::ulid(),
            'organization_id'        => $org->id,
            'name'                   => 'Sapphire Bay Resort',
            'code'                   => 'SBR',
            'slug'                   => 'sapphire-bay',
            'currency'               => 'USD',
            'timezone'               => 'UTC',
            'status'                 => 'active',
            'booking_engine_enabled' => true,
        ]);

        $rt = RoomType::create([
            'ulid'          => (string) Str::ulid(),
            'property_id'   => $this->property->id,
            'name'          => 'Executive Ocean View',
            'code'          => 'EOV',
            'max_occupancy' => 2,
            'status'        => 'active',
        ]);

        $this->room1 = Room::create([
            'ulid'         => (string) Str::ulid(),
            'property_id'  => $this->property->id,
            'room_type_id' => $rt->id,
            'number'       => '201',
            'status'       => 'clean',
        ]);

        $this->room2 = Room::create([
            'ulid'         => (string) Str::ulid(),
            'property_id'  => $this->property->id,
            'room_type_id' => $rt->id,
            'number'       => '202',
            'status'       => 'clean',
        ]);

        $guest = GuestProfile::create([
            'ulid'            => (string) Str::ulid(),
            'organization_id' => $org->id,
            'first_name'      => 'Michael',
            'last_name'       => 'Scott',
            'email'           => 'michael.scott@example.com',
        ]);

        $ratePlan = RatePlan::create([
            'ulid'               => (string) Str::ulid(),
            'property_id'        => $this->property->id,
            'name'               => 'Standard Rate',
            'code'               => 'STD',
            'default_rate_minor' => 10000,
            'currency'           => 'USD',
            'status'             => 'active',
        ]);

        $this->reservation = Reservation::create([
            'ulid'                => (string) Str::ulid(),
            'confirmation_number' => 'SB001-202608-TEST',
            'organization_id'     => $org->id,
            'property_id'         => $this->property->id,
            'primary_guest_id'    => $guest->id,
            'rate_plan_id'        => $ratePlan->id,
            'status'              => 'confirmed',
            'check_in'            => now()->toDateString(),
            'check_out'           => now()->addDays(2)->toDateString(),
            'nights'              => 2,
            'rooms_count'         => 1,
            'adults'              => 1,
            'children'            => 0,
            'currency'            => 'USD',
            'subtotal_minor'      => 20000,
            'tax_minor'           => 2000,
            'total_minor'         => 22000,
            'balance_minor'       => 22000,
            'booking_channel'     => 'staff',
        ]);
    }

    public function test_front_desk_can_view_tape_chart_and_arrivals_roster()
    {
        $response1 = $this->actingAs($this->user)->get(route('admin.tape-chart.index'));
        $response1->assertStatus(200);
        $response1->assertSee('Sapphire Bay Resort');

        $response2 = $this->actingAs($this->user)->get(route('admin.front-desk.arrivals'));
        $response2->assertStatus(200);
        $response2->assertSee('SB001-202608-TEST');
    }

    public function test_staff_can_check_in_guest_and_create_stay()
    {
        $response = $this->actingAs($this->user)->post(route('admin.front-desk.check-in', $this->reservation), [
            'room_id'   => $this->room1->id,
            'id_type'   => 'passport',
            'id_number' => 'P1234567',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stays', [
            'reservation_id' => $this->reservation->id,
            'room_id'        => $this->room1->id,
            'status'         => 'checked_in',
        ]);

        $this->reservation->refresh();
        $this->assertEquals('checked_in', $this->reservation->status);

        $this->room1->refresh();
        $this->assertEquals('occupied', $this->room1->status);
    }

    public function test_staff_can_check_out_guest_and_mark_room_dirty()
    {
        // 1. Check in first
        $service = app(StayManagementService::class);
        $stay    = $service->executeCheckIn($this->reservation, $this->room1, [], $this->user);

        // 2. Check out
        $response = $this->actingAs($this->user)->post(route('admin.front-desk.check-out', $stay));

        $response->assertRedirect();
        $stay->refresh();
        $this->assertEquals('checked_out', $stay->status);

        $this->reservation->refresh();
        $this->assertEquals('checked_out', $this->reservation->status);

        $this->room1->refresh();
        $this->assertEquals('dirty', $this->room1->status);
    }

    public function test_staff_can_execute_room_move()
    {
        $service = app(StayManagementService::class);
        $stay    = $service->executeCheckIn($this->reservation, $this->room1, [], $this->user);

        $response = $this->actingAs($this->user)->post(route('admin.front-desk.move-room', $stay), [
            'new_room_id' => $this->room2->id,
            'reason'      => 'Guest requested higher floor',
        ]);

        $response->assertRedirect();
        $stay->refresh();
        $this->assertEquals($this->room2->id, $stay->room_id);

        $this->room1->refresh();
        $this->assertEquals('dirty', $this->room1->status);

        $this->room2->refresh();
        $this->assertEquals('occupied', $this->room2->status);
    }

    public function test_staff_can_mark_reservation_as_no_show()
    {
        $response = $this->actingAs($this->user)->post(route('admin.front-desk.no-show', $this->reservation));

        $response->assertRedirect();
        $this->reservation->refresh();
        $this->assertEquals('no_show', $this->reservation->status);
    }
}
