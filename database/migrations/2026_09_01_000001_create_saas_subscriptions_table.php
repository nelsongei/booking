<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('saas_subscriptions')) {
            Schema::create('saas_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->string('plan_tier')->default('basic'); // basic, professional, enterprise
                $table->string('status')->default('active'); // trialing, active, past_due, canceled
                $table->integer('max_properties')->default(1);
                $table->integer('max_rooms_per_property')->default(50);
                $table->json('features_enabled')->nullable(); // ['pos', 'momo_payments', 'channel_manager', 'rms']
                $table->date('current_period_start');
                $table->date('current_period_end');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_subscriptions');
    }
};
