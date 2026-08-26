<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ticket_types')) {
            Schema::create('ticket_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 12, 2);
                $table->integer('quota')->default(100);
                $table->string('status', 20)->default('ACTIVE'); // ACTIVE, INACTIVE
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('order_code', 64)->unique()->index();
                $table->date('visit_date')->index();
                $table->string('customer_name');
                $table->string('customer_email');
                $table->string('customer_phone');
                $table->integer('total_quantity')->default(1);
                $table->decimal('total_amount', 14, 2)->default(0);
                $table->string('status', 20)->default('PENDING')->index(); // PENDING, PAID, CANCELLED, EXPIRED
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('ticket_type_id')->constrained('ticket_types')->restrictOnDelete();
                $table->integer('quantity')->default(1);
                $table->decimal('price', 12, 2);
                $table->decimal('subtotal', 14, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('ticket_types');
    }
};
