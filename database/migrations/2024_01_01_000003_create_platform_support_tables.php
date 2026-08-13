<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->timestamps();

            $table->unique(['property_id', 'key']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('correlation_id', 36)->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('property_id')->nullable()->index();
            $table->unsignedBigInteger('actor_user_id')->nullable()->index();
            $table->string('actor_type')->default('user'); // user, system, integration, guest
            $table->string('action'); // e.g. "reservation.created", "user.login"
            $table->string('target_type')->nullable(); // e.g. "Reservation"
            $table->string('target_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('metadata')->nullable();
            $table->string('source_ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at');

            $table->index(['organization_id', 'action', 'created_at']);
            $table->index(['property_id', 'action', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 128);
            $table->string('operation', 64); // e.g. "reservation.create"
            $table->string('client_scope', 64)->nullable(); // org_id or api_client_id
            $table->string('fingerprint', 64); // hash of request body
            $table->string('status')->default('processing'); // processing, completed, failed
            $table->integer('response_status')->nullable();
            $table->json('response_body')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['key', 'operation', 'client_scope']);
            $table->index('expires_at');
        });

        Schema::create('api_clients', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('client_id', 64)->unique();
            $table->string('client_secret_hash'); // hashed secret
            $table->string('status')->default('active');
            $table->json('scopes')->nullable();
            $table->json('allowed_ips')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_clients');
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('property_settings');
    }
};
