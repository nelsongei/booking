<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventory per room type per day — the source of truth for availability
        Schema::create('inventory_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('total')->default(0);       // total physical rooms of this type
            $table->integer('blocked')->default(0);     // OOO/maintenance blocks
            $table->integer('sold')->default(0);        // confirmed reservations
            $table->integer('holds')->default(0);       // active (non-expired) holds
            $table->integer('protected')->default(0);   // protected for walk-ins etc.
            $table->integer('overbooking_allowed')->default(0);
            $table->timestamps();

            $table->unique(['property_id', 'room_type_id', 'date']);
            $table->index(['property_id', 'date']);
        });

        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type'); // add, remove, block, unblock, protect, overbooking
            $table->integer('quantity');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['property_id', 'room_type_id', 'date']);
        });

        // Inventory holds — transient reservation of inventory during booking
        Schema::create('inventory_holds', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('reservation_ulid')->nullable()->index(); // set after reservation created
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('rooms_count')->default(1);
            $table->string('status')->default('active'); // active, converted, released, expired
            $table->string('source')->default('booking_engine'); // booking_engine, staff, channel
            $table->string('session_token', 64)->nullable()->index(); // guest session
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['property_id', 'room_type_id', 'status']);
            $table->index(['status', 'expires_at']); // for expiry cleanup job
        });

        // Quoted prices — snapshot of a price calculation
        Schema::create('rate_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->json('input'); // search params snapshot
            $table->json('output'); // full quote with nightly breakdown
            $table->json('trace')->nullable(); // pricing engine calculation trace
            $table->string('promo_code')->nullable();
            $table->string('currency', 3);
            $table->integer('total_minor');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['property_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_quotes');
        Schema::dropIfExists('inventory_holds');
        Schema::dropIfExists('inventory_adjustments');
        Schema::dropIfExists('inventory_days');
    }
};
