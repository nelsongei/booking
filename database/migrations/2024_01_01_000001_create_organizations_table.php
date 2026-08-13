<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('legal_name')->nullable();
            $table->string('tax_identifier')->nullable();
            $table->string('default_currency', 3)->default('USD');
            $table->string('default_timezone')->default('UTC');
            $table->string('default_locale', 10)->default('en');
            $table->string('status')->default('active'); // active, suspended, trial
            $table->json('settings')->nullable();
            $table->text('address')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['slug', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
