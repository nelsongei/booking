<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable(); // bathroom, view, tech, food, wellness
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 10)->nullable();
            $table->timestamps();

            $table->index('property_id');
        });

        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->integer('level')->default(0);
            $table->timestamps();

            $table->index(['property_id', 'level']);
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('bed_type')->nullable(); // king, queen, twin, double, single
            $table->integer('base_occupancy')->default(2);
            $table->integer('max_adults')->default(2);
            $table->integer('max_children')->default(0);
            $table->integer('max_occupancy')->default(2);
            $table->integer('size_sqm')->nullable();
            $table->string('view')->nullable(); // sea, garden, pool, city
            $table->boolean('is_accessible')->default(false);
            $table->boolean('smoking_allowed')->default(false);
            $table->string('status')->default('active'); // active, inactive
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'code']);
            $table->index(['property_id', 'status']);
        });

        Schema::create('room_type_amenities', function (Blueprint $table) {
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->primary(['room_type_id', 'amenity_id']);
        });

        Schema::create('room_type_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['room_type_id', 'sort_order']);
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('floor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('room_number', 20);
            $table->string('name')->nullable(); // e.g. "Ocean Suite 101"
            $table->string('status')->default('clean'); // clean, dirty, inspected, out_of_order, out_of_service
            $table->boolean('is_smoking')->default(false);
            $table->json('features')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'room_number']);
            $table->index(['property_id', 'room_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_type_images');
        Schema::dropIfExists('room_type_amenities');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('floors');
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('amenities');
    }
};
