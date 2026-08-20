<?php

namespace Tests\Feature\Security;

use App\Infrastructure\Persistence\FolioAccount;
use App\Infrastructure\Persistence\GuestProfile;
use App\Infrastructure\Persistence\Organization;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\PropertyUserAssignment;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected Organization $orgA;
    protected Property $propertyA;
    protected User $userA;
    protected Reservation $reservationA;

    protected Organization $orgB;
    protected Property $propertyB;
    protected User $userB;
    protected Reservation $reservationB;

    protected function setUp(): void
    {
        parent::setUp();

        // Organization & Property A
        $this->orgA = Organization::create([
            'ulid' => (string) Str::ulid(),
            'name' => 'Safari Lodges Org A',
            'code' => 'SLA',
            'status' => 'active',
        ]);

        $this->propertyA = Property::create([
            'ulid'                   => (string) Str::ulid(),
            'organization_id'        => $this->orgA->id,
            'name'                   => 'Mara Lodge Property A',
            'code'                   => 'MLA',
            'slug'                   => 'mara-lodge-a',
            'currency'               => 'USD',
            'timezone'               => 'Africa/Nairobi',
            'status'                 => 'active',
            'booking_engine_enabled' => true,
        ]);

        $this->userA = User::factory()->create([
            'organization_id' => $this->orgA->id,
            'is_platform_admin' => false,
        ]);

        PropertyUserAssignment::create([
            'user_id' => $this->userA->id,
            'property_id' => $this->propertyA->id,
            'organization_id' => $this->orgA->id,
            'role_name' => 'front-desk-agent',
            'is_active' => true,
        ]);

        $guestA = GuestProfile::create([
            'ulid' => (string) Str::ulid(),
            'organization_id' => $this->orgA->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

        $this->reservationA = Reservation::create([
            'ulid' => (string) Str::ulid(),
            'confirmation_number' => 'MLA001-202608-AAA1',
            'organization_id' => $this->orgA->id,
            'property_id' => $this->propertyA->id,
            'primary_guest_id' => $guestA->id,
            'status' => 'confirmed',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'nights' => 2,
            'rooms_count' => 1,
            'adults' => 1,
            'children' => 0,
            'currency' => 'USD',
            'subtotal_minor' => 20000,
            'tax_minor' => 2000,
            'total_minor' => 22000,
            'balance_minor' => 22000,
            'booking_channel' => 'staff',
        ]);

        // Organization & Property B
        $this->orgB = Organization::create([
            'ulid' => (string) Str::ulid(),
            'name' => 'Resorts Group Org B',
            'code' => 'RGB',
            'status' => 'active',
        ]);

        $this->propertyB = Property::create([
            'ulid'                   => (string) Str::ulid(),
            'organization_id'        => $this->orgB->id,
            'name'                   => 'Zanzibar Beach Property B',
            'code'                   => 'ZBP',
            'slug'                   => 'zanzibar-beach-b',
            'currency'               => 'USD',
            'timezone'               => 'Africa/Dar_es_Salaam',
            'status'                 => 'active',
            'booking_engine_enabled' => true,
        ]);

        $this->userB = User::factory()->create([
            'organization_id' => $this->orgB->id,
            'is_platform_admin' => false,
        ]);

        PropertyUserAssignment::create([
            'user_id' => $this->userB->id,
            'property_id' => $this->propertyB->id,
            'organization_id' => $this->orgB->id,
            'role_name' => 'front-desk-agent',
            'is_active' => true,
        ]);

        $guestB = GuestProfile::create([
            'ulid' => (string) Str::ulid(),
            'organization_id' => $this->orgB->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
        ]);

        $this->reservationB = Reservation::create([
            'ulid' => (string) Str::ulid(),
            'confirmation_number' => 'ZBP001-202608-BBB2',
            'organization_id' => $this->orgB->id,
            'property_id' => $this->propertyB->id,
            'primary_guest_id' => $guestB->id,
            'status' => 'confirmed',
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(3)->toDateString(),
            'nights' => 3,
            'rooms_count' => 1,
            'adults' => 2,
            'children' => 0,
            'currency' => 'USD',
            'subtotal_minor' => 30000,
            'tax_minor' => 3000,
            'total_minor' => 33000,
            'balance_minor' => 33000,
            'booking_channel' => 'staff',
        ]);
    }

    public function test_user_can_access_own_property_reservation()
    {
        $response = $this->actingAs($this->userA)
            ->withSession(['current_property_id' => $this->propertyA->id])
            ->get(route('admin.reservations.show', $this->reservationA));

        $response->assertStatus(200);
        $response->assertSee($this->reservationA->confirmation_number);
    }

    public function test_user_cannot_access_other_organization_reservation()
    {
        $response = $this->actingAs($this->userA)
            ->withSession(['current_property_id' => $this->propertyA->id])
            ->get(route('admin.reservations.show', $this->reservationB));

        // Expect HTTP 403 or 404 forbidden/not found due to property scoping
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    public function test_session_current_property_cannot_be_set_to_unauthorized_property()
    {
        // User A attempts to set session property_id to Property B (in Org B)
        $response = $this->actingAs($this->userA)
            ->withSession(['current_property_id' => $this->propertyB->id])
            ->get(route('admin.reservations.index'));

        $response->assertStatus(200);

        // Verify container bound property is Property A, NOT Property B
        $currentProperty = app()->bound('current.property') ? app('current.property') : null;
        $this->assertNotNull($currentProperty);
        $this->assertEquals($this->propertyA->id, $currentProperty->id);
        $this->assertNotEquals($this->propertyB->id, $currentProperty->id);
    }

    public function test_can_access_property_returns_false_for_unassigned_property()
    {
        $this->assertTrue($this->userA->canAccessProperty($this->propertyA));
        $this->assertFalse($this->userA->canAccessProperty($this->propertyB));
    }
}
