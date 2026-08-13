<?php

namespace Tests\Feature;

use App\Domain\Folios\FolioLedgerService;
use App\Infrastructure\Persistence\ChargeCode;
use App\Infrastructure\Persistence\FolioAccount;
use App\Infrastructure\Persistence\FolioTransaction;
use App\Infrastructure\Persistence\GuestProfile;
use App\Infrastructure\Persistence\Organization;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FolioAndCashieringTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Property $property;
    protected Reservation $reservation;
    protected ChargeCode $chargeCode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $org = Organization::create([
            'ulid' => (string) Str::ulid(),
            'name' => 'Grand Regal Group',
            'code' => 'GRG',
            'status' => 'active',
        ]);

        $this->property = Property::create([
            'ulid'                   => (string) Str::ulid(),
            'organization_id'        => $org->id,
            'name'                   => 'Grand Regal Palace',
            'code'                   => 'GRP',
            'slug'                   => 'grand-regal',
            'currency'               => 'USD',
            'timezone'               => 'UTC',
            'status'                 => 'active',
            'booking_engine_enabled' => true,
        ]);

        $guest = GuestProfile::create([
            'ulid'            => (string) Str::ulid(),
            'organization_id' => $org->id,
            'first_name'      => 'David',
            'last_name'       => 'Miller',
            'email'           => 'david.m@example.com',
        ]);

        $this->reservation = Reservation::create([
            'ulid'                => (string) Str::ulid(),
            'confirmation_number' => 'GR001-202608-TEST',
            'organization_id'     => $org->id,
            'property_id'         => $this->property->id,
            'primary_guest_id'    => $guest->id,
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

        $this->chargeCode = ChargeCode::create([
            'property_id' => $this->property->id,
            'code'        => 'RM-STAY',
            'name'        => 'Room Charge',
            'category'    => 'room',
            'is_taxable'  => true,
            'is_active'   => true,
        ]);
    }

    public function test_folio_ledger_service_creates_folio_and_posts_charges()
    {
        $service = app(FolioLedgerService::class);

        $folio = $service->getOrCreateFolio($this->reservation);
        $this->assertNotNull($folio);
        $this->assertEquals('guest', $folio->type);

        $tx = $service->postCharge($folio, $this->chargeCode, 15000, 'Room Charge Night 1', null, $this->user);
        $this->assertEquals(15000, $tx->amount_minor);
        $this->assertEquals(15000, $folio->fresh()->balance_minor);
    }

    public function test_reversal_appends_inverse_transaction_without_mutating_original()
    {
        $service = app(FolioLedgerService::class);

        $folio = $service->getOrCreateFolio($this->reservation);
        $tx    = $service->postCharge($folio, $this->chargeCode, 10000, 'Restaurant Charge', null, $this->user);

        $reversal = $service->reverseTransaction($tx, 'Guest billed by mistake', $this->user);

        $this->assertEquals(-10000, $reversal->amount_minor);
        $this->assertEquals('reversal', $reversal->type);
        $this->assertEquals(0, $folio->fresh()->balance_minor);

        // Original transaction remains unchanged
        $this->assertEquals(10000, $tx->fresh()->amount_minor);
    }

    public function test_cashier_shift_open_close_and_variance_calculation()
    {
        $service = app(FolioLedgerService::class);

        $shift = $service->openCashierShift($this->property, $this->user, 10000); // $100.00 float
        $this->assertEquals('open', $shift->status);
        $this->assertEquals(10000, $shift->opening_balance_minor);

        // Close shift with $100.00 count (0 variance)
        $service->closeCashierShift($shift, 10000, 'Shift closed smoothly.');
        $shift->refresh();

        $this->assertEquals('closed', $shift->status);
        $this->assertEquals(10000, $shift->expected_closing_minor);
        $this->assertEquals(0, $shift->variance_minor);
    }

    public function test_staff_can_view_folio_and_post_charge_via_http()
    {
        $service = app(FolioLedgerService::class);
        $folio   = $service->getOrCreateFolio($this->reservation);

        $response = $this->actingAs($this->user)->get(route('admin.folios.show', $folio));
        $response->assertStatus(200);

        $postResponse = $this->actingAs($this->user)->post(route('admin.folios.charge', $folio), [
            'charge_code_id' => $this->chargeCode->id,
            'amount'         => 50.00,
            'description'    => 'Laundry Service',
        ]);

        $postResponse->assertRedirect();
        $this->assertEquals(5000, $folio->fresh()->balance_minor);
    }
}
