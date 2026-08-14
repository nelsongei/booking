<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Housekeeping tasks
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // checkout_clean, stayover_clean, turndown, inspection, special
            $table->string('status')->default('pending'); // pending, in_progress, completed, skipped
            $table->string('priority')->default('normal'); // urgent, high, normal, low
            $table->date('due_date');
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'due_date', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        Schema::create('housekeeping_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->default('staff'); // staff, system, pms
            $table->text('notes')->nullable();
            $table->timestamp('changed_at');

            $table->index(['room_id', 'changed_at']);
            $table->index(['property_id', 'changed_at']);
        });

        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category')->nullable(); // plumbing, electrical, hvac, furniture
            $table->string('priority')->default('normal');
            $table->string('status')->default('open'); // open, in_progress, completed, deferred
            $table->text('description');
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
        });

        // Night audit
        Schema::create('night_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('business_date');
            $table->string('status')->default('pending'); // pending, in_progress, completed, failed
            $table->json('steps')->nullable(); // step statuses: {validate: done, post_charges: done, ...}
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('failure_notes')->nullable();
            $table->json('report_data')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'business_date']); // can only run once per date
            $table->index(['property_id', 'status']);
        });

        Schema::create('business_date_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('business_date');
            $table->date('previous_business_date')->nullable();
            $table->foreignId('rolled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rolled_at');

            $table->index(['property_id', 'business_date']);
        });

        Schema::create('operational_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shift')->nullable(); // morning, afternoon, night
            $table->date('business_date');
            $table->text('content');
            $table->timestamps();

            $table->index(['property_id', 'business_date']);
        });

        // Email messages
        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('template');
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->string('subject');
            $table->string('provider')->nullable(); // smtp, mailgun, sendgrid
            $table->string('provider_message_id')->nullable();
            $table->string('status')->default('queued'); // queued, sent, failed, bounced
            $table->integer('attempts')->default(0);
            $table->string('failure_reason')->nullable();
            $table->string('related_type')->nullable(); // Reservation
            $table->string('related_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
            $table->index(['status', 'attempts']);
        });

        // Integration layer
        Schema::create('integration_connections', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // stripe, channel_manager, accounting
            $table->string('status')->default('inactive'); // active, inactive, error
            $table->json('credentials_encrypted')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('provider');
            $table->string('event_type');
            $table->string('provider_event_id')->nullable();
            $table->json('payload');
            $table->string('status')->default('pending'); // pending, processing, processed, failed
            $table->integer('attempts')->default(0);
            $table->string('failure_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'provider_event_id']); // dedup
            $table->index(['status', 'attempts']);
        });

        Schema::create('dead_letter_items', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // job_class, webhook, integration
            $table->string('reason');
            $table->json('payload');
            $table->integer('attempts')->default(0);
            $table->string('status')->default('pending'); // pending, replayed, resolved, discarded
            $table->text('notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['source', 'status']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('dead_letter_items');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('integration_connections');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('operational_notes');
        Schema::dropIfExists('business_date_history');
        Schema::dropIfExists('night_audits');
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('housekeeping_status_history');
        Schema::dropIfExists('housekeeping_tasks');
        Schema::enableForeignKeyConstraints();
    }
};
