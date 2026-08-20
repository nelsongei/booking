<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('digital_registration_cards')) {
            Schema::create('digital_registration_cards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
                $table->string('passport_number')->nullable();
                $table->string('nationality')->nullable();
                $table->string('expected_arrival_time')->nullable();
                $table->text('dietary_preferences')->nullable();
                $table->longText('digital_signature')->nullable();
                $table->timestamp('terms_consented_at')->nullable();
                $table->string('id_document_path')->nullable();
                $table->timestamp('id_retention_until')->nullable();
                $table->string('status')->default('completed'); // pending, completed
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('guest_service_requests')) {
            Schema::create('guest_service_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
                $table->string('category'); // housekeeping, maintenance, room_service, concierge
                $table->text('details');
                $table->string('status')->default('pending'); // pending, in_progress, completed
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_service_requests');
        Schema::dropIfExists('digital_registration_cards');
    }
};
