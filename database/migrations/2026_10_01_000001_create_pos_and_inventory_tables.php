<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pos_outlets')) {
            Schema::create('pos_outlets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code')->nullable();
                $table->string('type')->default('restaurant'); // restaurant, bar, spa, shop, minibar
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pos_menu_items')) {
            Schema::create('pos_menu_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('pos_outlet_id')->constrained('pos_outlets')->cascadeOnDelete();
                $table->string('name');
                $table->string('category')->default('food'); // food, beverage, service
                $table->integer('price_minor');
                $table->boolean('is_taxable')->default(true);
                $table->boolean('is_available')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pos_orders')) {
            Schema::create('pos_orders', function (Blueprint $table) {
                $table->id();
                $table->string('ulid', 26)->unique();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->foreignId('pos_outlet_id')->constrained('pos_outlets')->cascadeOnDelete();
                $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('folio_account_id')->nullable()->constrained('folio_accounts')->nullOnDelete();
                $table->foreignId('server_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('order_type')->default('dine_in'); // dine_in, takeaway, room_charge
                $table->string('table_number')->nullable();
                $table->integer('covers')->default(1);
                $table->string('status')->default('open'); // open, fulfilled, billed, voided
                $table->string('payment_status')->default('unpaid'); // unpaid, paid, posted_to_room
                $table->string('payment_method')->nullable(); // room_charge, cash, card, mobile_money
                $table->integer('subtotal_minor')->default(0);
                $table->integer('tax_minor')->default(0);
                $table->integer('total_minor')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pos_order_items')) {
            Schema::create('pos_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pos_order_id')->constrained('pos_orders')->cascadeOnDelete();
                $table->foreignId('pos_menu_item_id')->constrained('pos_menu_items')->cascadeOnDelete();
                $table->string('item_name');
                $table->integer('quantity')->default(1);
                $table->integer('unit_price_minor');
                $table->integer('subtotal_minor');
                $table->integer('tax_minor')->default(0);
                $table->integer('total_minor');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stock_items')) {
            Schema::create('stock_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('unit_of_measure'); // kg, liters, pieces, bottles
                $table->decimal('quantity_on_hand', 12, 3)->default(0);
                $table->decimal('reorder_level', 12, 3)->default(10);
                $table->integer('unit_cost_minor')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('recipes')) {
            Schema::create('recipes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pos_menu_item_id')->constrained('pos_menu_items')->cascadeOnDelete();
                $table->foreignId('stock_item_id')->constrained('stock_items')->cascadeOnDelete();
                $table->decimal('quantity_used', 12, 3);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('stock_items');
        Schema::dropIfExists('pos_order_items');
        Schema::dropIfExists('pos_orders');
        Schema::dropIfExists('pos_menu_items');
        Schema::dropIfExists('pos_outlets');
        Schema::enableForeignKeyConstraints();
    }
};
