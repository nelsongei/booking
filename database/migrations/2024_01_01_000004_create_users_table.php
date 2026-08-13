<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status')->default('active'); // active, suspended, invited
            $table->boolean('is_platform_admin')->default(false);
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_secret')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
