<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Meal plans lookup
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // RO, BB, HB, FB, AI
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Cancellation policies
        Schema::create('cancellation_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->json('rules'); // [{hours_before: 48, penalty_type: "percentage", penalty_value: 100}]
            $table->boolean('is_non_refundable')->default(false);
            $table->timestamps();

            $table->unique(['property_id', 'code']);
        });

        // Deposit policies
        Schema::create('deposit_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->string('type'); // none, percentage, fixed_amount, first_night, full_amount
            $table->integer('amount_minor')->default(0); // for fixed_amount
            $table->string('currency', 3)->nullable();
            $table->decimal('percentage', 5, 2)->nullable(); // for percentage
            $table->string('collection_timing')->default('at_booking'); // at_booking, on_arrival
            $table->timestamps();
        });

        // Taxes and fees
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->string('type'); // percentage, fixed_per_night, fixed_per_stay, fixed_per_person
            $table->decimal('rate', 8, 4); // rate or amount depending on type
            $table->string('currency', 3)->nullable();
            $table->boolean('is_included_in_rate')->default(false);
            $table->boolean('applies_to_extras')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['property_id', 'code']);
            $table->index(['property_id', 'is_active']);
        });

        // Child age bands
        Schema::create('child_age_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Infant", "Child", "Teen"
            $table->integer('min_age');
            $table->integer('max_age');
            $table->decimal('rate_multiplier', 4, 2)->default(0.00); // 0 = free, 0.5 = half price, 1.0 = full
            $table->boolean('requires_bed')->default(false);
            $table->timestamps();

            $table->index('property_id');
        });

        // Rate plans
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->foreignId('meal_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cancellation_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('deposit_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_public')->default(true); // visible on booking engine
            $table->boolean('is_refundable')->default(true);
            $table->boolean('breakfast_included')->default(false);
            $table->integer('min_advance_days')->default(0);
            $table->integer('max_advance_days')->nullable();
            $table->json('channel_restrictions')->nullable(); // which channels can sell this
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'code']);
            $table->index(['property_id', 'is_active', 'is_public']);
        });

        // Map rate plans to room types
        Schema::create('rate_plan_room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['rate_plan_id', 'room_type_id']);
        });

        // Daily rates — the core rate store
        Schema::create('rate_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('amount_minor'); // in currency minor units (cents)
            $table->string('currency', 3);
            $table->integer('extra_adult_minor')->default(0);
            $table->integer('extra_child_minor')->default(0);
            $table->timestamps();

            $table->unique(['rate_plan_id', 'room_type_id', 'date']);
            $table->index(['property_id', 'room_type_id', 'date']);
        });

        // Rate restrictions (stop-sell, min-stay, CTA, CTD)
        Schema::create('rate_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->boolean('is_closed')->default(false); // stop-sell
            $table->boolean('closed_to_arrival')->default(false);
            $table->boolean('closed_to_departure')->default(false);
            $table->integer('min_stay')->nullable();
            $table->integer('max_stay')->nullable();
            $table->integer('min_advance_booking')->nullable(); // days ahead
            $table->timestamps();

            $table->unique(['rate_plan_id', 'room_type_id', 'date']);
            $table->index(['property_id', 'date']);
        });

        // Promotions and promo codes
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type'); // percentage, fixed_amount, free_nights
            $table->decimal('discount_value', 10, 4);
            $table->string('currency', 3)->nullable();
            $table->string('applies_to')->default('total'); // total, room_only, first_night
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->date('stay_from')->nullable();
            $table->date('stay_until')->nullable();
            $table->integer('min_nights')->nullable();
            $table->integer('min_advance_days')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('uses_count')->default(0);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('promotion_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->integer('max_uses')->nullable();
            $table->integer('uses_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['promotion_id', 'code']);
            $table->index('code');
        });

        // Out of order and maintenance blocks
        Schema::create('out_of_order_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete(); // specific room
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete(); // or whole type
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('rooms_count')->default(1); // if room_type level block
            $table->string('reason')->nullable();
            $table->string('type')->default('out_of_order'); // out_of_order, out_of_service
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['property_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('out_of_order_blocks');
        Schema::dropIfExists('promotion_codes');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('rate_restrictions');
        Schema::dropIfExists('rate_days');
        Schema::dropIfExists('rate_plan_room_types');
        Schema::dropIfExists('rate_plans');
        Schema::dropIfExists('child_age_bands');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('deposit_policies');
        Schema::dropIfExists('cancellation_policies');
        Schema::dropIfExists('meal_plans');
    }
};
