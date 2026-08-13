<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('code')->unique();
            $table->string('tax_id')->nullable();
            $table->integer('credit_limit_minor')->default(0);
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('status')->default('active'); // active, suspended, closed
            $table->timestamps();

            $table->index(['property_id', 'status']);
        });

        Schema::create('group_allotments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('corporate_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('rooms_allocated');
            $table->integer('rooms_picked_up')->default(0);
            $table->integer('negotiated_rate_minor')->default(0);
            $table->string('status')->default('active'); // active, released, completed
            $table->timestamps();

            $table->index(['property_id', 'start_date', 'end_date']);
        });

        Schema::create('loyalty_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_profile_id')->constrained()->cascadeOnDelete();
            $table->string('account_number')->unique();
            $table->string('tier')->default('bronze'); // bronze, silver, gold, platinum
            $table->integer('points_balance')->default(0);
            $table->integer('lifetime_points')->default(0);
            $table->timestamp('joined_at');
            $table->timestamps();
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // earn, redeem, adjustment
            $table->integer('points'); // positive for earn, negative for redeem
            $table->string('description');
            $table->string('reference_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_accounts');
        Schema::dropIfExists('group_allotments');
        Schema::dropIfExists('corporate_accounts');
    }
};
