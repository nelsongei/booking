<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stays — created when a reservation room is checked in
        Schema::create('stays', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete(); // physical room assigned
            $table->string('status')->default('expected'); // expected, checked_in, checked_out, no_show
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['property_id', 'status', 'arrival_date']);
            $table->index(['reservation_id']);
        });

        // Room assignments — maps a stay to a physical room with dates
        Schema::create('room_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // No overlapping active assignments for same physical room
            $table->index(['room_id', 'start_date', 'end_date']);
            $table->index(['property_id', 'is_active']);
        });

        // Check-in records — details captured at check-in time
        Schema::create('checkin_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_id')->constrained()->cascadeOnDelete();
            $table->string('id_type')->nullable(); // passport, national_id
            $table->string('id_number')->nullable();
            $table->string('id_country', 2)->nullable();
            $table->date('id_expiry')->nullable();
            $table->string('guest_signature_path')->nullable();
            $table->json('additional_guests')->nullable(); // sharers not in guest profiles
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Folio accounts
        Schema::create('folio_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stay_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('guest'); // guest, company, agent, deposit
            $table->string('status')->default('open'); // open, closed
            $table->string('currency', 3);
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('travel_agent_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['reservation_id', 'type']);
        });

        // Folio windows — routing buckets within a folio
        Schema::create('folio_windows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_account_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('Main');
            $table->integer('window_number')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['folio_account_id', 'window_number']);
        });

        // Charge codes
        Schema::create('charge_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->string('category'); // room, food, beverage, spa, tax, fee, adjustment, other
            $table->string('revenue_category')->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['property_id', 'code']);
        });

        // Folio transactions — APPEND ONLY, never update, use reversals
        Schema::create('folio_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('folio_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folio_window_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // charge, payment, adjustment, reversal, transfer, deposit
            $table->foreignId('charge_code_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->integer('amount_minor'); // positive = charge, negative = credit
            $table->string('currency', 3);
            $table->foreignId('reverses_transaction_id')->nullable()->constrained('folio_transactions')->nullOnDelete();
            $table->string('reversal_reason')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at');
            $table->date('business_date');
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['folio_account_id', 'type', 'business_date']);
            $table->index(['property_id', 'business_date']);
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('folio_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // stripe, cash, bank_transfer, other
            $table->string('provider_payment_id')->nullable();
            $table->string('provider_customer_id')->nullable();
            $table->string('provider_payment_method')->nullable();
            $table->integer('amount_minor');
            $table->string('currency', 3);
            $table->string('status'); // pending, authorized, captured, failed, cancelled, refunded
            $table->string('type')->default('capture'); // capture, preauth
            $table->json('provider_metadata')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reservation_id', 'status']);
            $table->index(['provider', 'provider_payment_id']);
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('provider_session_id')->nullable();
            $table->string('status');
            $table->json('raw_response')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('provider_refund_id')->nullable();
            $table->integer('amount_minor');
            $table->string('currency', 3);
            $table->string('reason');
            $table->string('status'); // pending, completed, failed
            $table->string('idempotency_key')->nullable()->unique();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folio_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number', 30)->unique();
            $table->string('type')->default('invoice'); // invoice, receipt, proforma
            $table->json('line_items');
            $table->integer('subtotal_minor');
            $table->integer('tax_minor');
            $table->integer('total_minor');
            $table->string('currency', 3);
            $table->string('pdf_path')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->index(['reservation_id']);
            $table->index(['property_id', 'issued_at']);
        });

        // Cashier shifts
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('open'); // open, closed
            $table->integer('opening_balance_minor')->default(0);
            $table->integer('closing_balance_minor')->nullable();
            $table->integer('expected_closing_minor')->nullable();
            $table->integer('variance_minor')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
        });

        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // receive, payout, float_adjustment
            $table->integer('amount_minor');
            $table->string('currency', 3);
            $table->string('reference')->nullable();
            $table->timestamp('transacted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('cashier_shifts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('folio_transactions');
        Schema::dropIfExists('charge_codes');
        Schema::dropIfExists('folio_windows');
        Schema::dropIfExists('folio_accounts');
        Schema::dropIfExists('checkin_records');
        Schema::dropIfExists('room_assignments');
        Schema::dropIfExists('stays');
    }
};
