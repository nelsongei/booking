<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20); // short property code e.g. "HTL01"
            $table->string('slug');
            $table->string('type')->default('hotel'); // hotel, resort, hostel, apartment
            $table->text('description')->nullable();
            $table->text('address_line1')->nullable();
            $table->text('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('timezone')->default('UTC');
            $table->string('locale', 10)->default('en');
            $table->integer('star_rating')->nullable();
            $table->string('status')->default('active'); // active, inactive, setup
            $table->boolean('booking_engine_enabled')->default(false);
            $table->string('booking_engine_slug')->nullable();
            $table->json('check_in_out_times')->nullable(); // {check_in: "14:00", check_out: "12:00"}
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'code']);
            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
