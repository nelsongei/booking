<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guest profiles
        Schema::create('guest_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('nationality', 2)->nullable();
            $table->string('language', 10)->default('en');
            $table->string('id_type')->nullable(); // passport, national_id, drivers_license
            $table->string('id_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('title')->nullable(); // Mr, Mrs, Ms, Dr
            $table->json('preferences')->nullable();
            $table->text('notes')->nullable();
            $table->integer('total_stays')->default(0);
            $table->integer('total_nights')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'email']);
            $table->index(['organization_id', 'last_name']);
        });

        Schema::create('guest_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_profile_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('home'); // home, work, billing
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('guest_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_profile_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // marketing_email, sms, data_processing
            $table->boolean('consented');
            $table->string('source')->nullable(); // booking_engine, front_desk, import
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();

            $table->index(['guest_profile_id', 'type']);
        });

        // Booking sources
        Schema::create('booking_sources', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('type'); // direct, ota, gds, corporate, agent, channel
            $table->string('channel')->nullable(); // booking_engine, phone, walk_in, email
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Companies / corporate accounts
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->string('tax_identifier')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->decimal('contracted_rate_discount', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'code']);
        });

        // Travel agents
        Schema::create('travel_agents', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('iata_number', 20)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Reservations
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('confirmation_number', 30)->unique()->nullable();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('primary_guest_id')->nullable()->constrained('guest_profiles')->nullOnDelete();
            $table->foreignId('booking_source_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('travel_agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('inquiry');
            // inquiry, held, pending_payment, confirmed, checked_in, checked_out, cancelled, no_show
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('nights');
            $table->integer('rooms_count')->default(1);
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->string('currency', 3);
            $table->integer('subtotal_minor');   // room charges before tax
            $table->integer('tax_minor');
            $table->integer('fee_minor')->default(0);
            $table->integer('discount_minor')->default(0);
            $table->integer('total_minor');
            $table->integer('deposit_minor')->default(0);
            $table->integer('balance_minor');
            $table->string('promo_code')->nullable();
            $table->json('policy_snapshot')->nullable(); // cancellation + deposit terms at booking time
            $table->text('special_requests')->nullable();
            $table->string('booking_channel')->nullable();
            $table->string('source_reference')->nullable(); // external booking ID
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'status', 'check_in']);
            $table->index(['property_id', 'check_in', 'check_out']);
            $table->index(['primary_guest_id']);
            $table->index(['organization_id', 'status']);
        });

        // Individual rooms within a reservation
        Schema::create('reservation_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->json('child_ages')->nullable();
            $table->string('status')->default('active');
            $table->integer('subtotal_minor');
            $table->integer('tax_minor');
            $table->integer('total_minor');
            $table->json('rate_snapshot')->nullable(); // nightly rates at booking time
            $table->json('policy_snapshot')->nullable();
            $table->timestamps();

            $table->index('reservation_id');
        });

        // Night-by-night rate breakdown (append-only price snapshot)
        Schema::create('reservation_nights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_room_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('rate_minor');
            $table->integer('tax_minor')->default(0);
            $table->integer('total_minor');
            $table->string('currency', 3);
            $table->json('breakdown')->nullable(); // base, occupancy supplement, discount, taxes
            $table->timestamps();

            $table->unique(['reservation_room_id', 'date']);
        });

        // Add-ons per reservation room
        Schema::create('reservation_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('unit_price_minor');
            $table->integer('total_minor');
            $table->string('currency', 3);
            $table->timestamps();
        });

        // Reservation status history
        Schema::create('reservation_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');

            $table->index('reservation_id');
        });

        // Reservation notes
        Schema::create('reservation_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('internal'); // internal, housekeeping, guest_request
            $table->boolean('is_alert')->default(false);
            $table->text('content');
            $table->timestamps();

            $table->index(['reservation_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_notes');
        Schema::dropIfExists('reservation_status_history');
        Schema::dropIfExists('reservation_addons');
        Schema::dropIfExists('reservation_nights');
        Schema::dropIfExists('reservation_rooms');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('travel_agents');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('booking_sources');
        Schema::dropIfExists('guest_consents');
        Schema::dropIfExists('guest_addresses');
        Schema::dropIfExists('guest_profiles');
    }
};
