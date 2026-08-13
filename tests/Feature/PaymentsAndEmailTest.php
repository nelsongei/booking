<?php

namespace Tests\Feature;

use App\Domain\Payments\InvoiceService;
use App\Domain\Payments\OfflinePaymentAdapter;
use App\Domain\Payments\StripeAdapter;
use App\Infrastructure\Persistence\GuestProfile;
use App\Infrastructure\Persistence\Invoice;
use App\Infrastructure\Persistence\Organization;
use App\Infrastructure\Persistence\Payment;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\Reservation;
use App\Infrastructure\Persistence\RoomType;
use App\Infrastructure\Persistence\User;
use App\Jobs\ProcessStripeWebhookJob;
use App\Mail\BookingConfirmationMail;
use App\Mail\PaymentReceiptMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentsAndEmailTest extends TestCase
{
    use RefreshDatabase;

    protected Property $property;
    protected Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();

        $org = Organization::create([
            'ulid' => (string) Str::ulid(),
            'name' => 'Royal Palms Group',
            'code' => 'RPG',
            'status' => 'active',
        ]);

        $this->property = Property::create([
            'ulid'                   => (string) Str::ulid(),
            'organization_id'        => $org->id,
            'name'                   => 'Royal Palms Luxury Suites',
            'code'                   => 'RPLS',
            'slug'                   => 'royal-palms',
            'currency'               => 'USD',
            'timezone'               => 'UTC',
            'status'                 => 'active',
            'booking_engine_enabled' => true,
        ]);

        $guest = GuestProfile::create([
            'ulid'            => (string) Str::ulid(),
            'organization_id' => $org->id,
            'first_name'      => 'Jane',
            'last_name'       => 'Doe',
            'email'           => 'jane.doe@example.com',
        ]);

        $this->reservation = Reservation::create([
            'ulid'                => (string) Str::ulid(),
            'confirmation_number' => 'RP001-202608-TEST',
            'organization_id'     => $org->id,
            'property_id'         => $this->property->id,
            'primary_guest_id'    => $guest->id,
            'status'              => 'confirmed',
            'check_in'            => now()->addDays(2)->toDateString(),
            'check_out'           => now()->addDays(4)->toDateString(),
            'nights'              => 2,
            'rooms_count'         => 1,
            'adults'              => 2,
            'children'            => 0,
            'currency'            => 'USD',
            'subtotal_minor'      => 30000,
            'tax_minor'           => 3000,
            'total_minor'         => 33000,
            'balance_minor'       => 33000,
            'booking_channel'     => 'direct_web',
        ]);
    }

    public function test_stripe_and_offline_adapters_create_intents()
    {
        $stripe = new StripeAdapter();
        $res1   = $stripe->createPaymentIntent($this->property, 10000, 'USD');
        $this->assertTrue($res1['success']);
        $this->assertNotNull($res1['payment_intent_id']);

        $offline = new OfflinePaymentAdapter();
        $res2    = $offline->createPaymentIntent($this->property, 5000, 'USD', ['provider' => 'cash']);
        $this->assertTrue($res2['success']);
        $this->assertEquals('succeeded', $res2['status']);
    }

    public function test_invoice_service_generates_pdf_file()
    {
        Storage::fake('local');
        $service = new InvoiceService();
        $invoice = $service->generateForReservation($this->reservation);

        $this->assertNotNull($invoice);
        $this->assertDatabaseHas('invoices', [
            'reservation_id' => $this->reservation->id,
            'invoice_number' => $invoice->invoice_number,
        ]);
        Storage::disk('local')->assertExists($invoice->pdf_path);
    }

    public function test_process_stripe_webhook_job_creates_payment_and_invoice()
    {
        Storage::fake('local');
        Mail::fake();

        $eventData = [
            'id'   => 'evt_test_123',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'id'              => 'pi_test_succeeded_999',
                'amount'          => 33000,
                'currency'        => 'usd',
                'metadata'        => [
                    'reservation_id' => $this->reservation->id,
                    'property_id'    => $this->property->id,
                ],
                'payment_method_types' => ['card'],
            ],
        ];

        $job = new ProcessStripeWebhookJob($eventData);
        $job->handle(new InvoiceService());

        $this->assertDatabaseHas('payments', [
            'reservation_id'      => $this->reservation->id,
            'provider_payment_id' => 'pi_test_succeeded_999',
            'amount_minor'        => 33000,
            'status'              => 'captured',
        ]);

        $this->reservation->refresh();
        $this->assertEquals(0, $this->reservation->balance_minor);

        Mail::assertSent(PaymentReceiptMail::class);
    }

    public function test_staff_can_record_payment_and_download_invoice()
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.reservations.payments.store', $this->reservation), [
            'amount'   => 150.00,
            'provider' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'reservation_id' => $this->reservation->id,
            'amount_minor'   => 15000,
            'provider'       => 'cash',
        ]);

        $dlResponse = $this->actingAs($user)->get(route('admin.reservations.invoice.download', $this->reservation));
        $dlResponse->assertStatus(200);
    }
}
