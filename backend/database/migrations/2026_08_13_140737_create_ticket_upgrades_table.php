<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('old_ticket_category_id')->constrained('ticket_categories')->restrictOnDelete();
            $table->foreignId('new_ticket_category_id')->constrained('ticket_categories')->restrictOnDelete();
            $table->decimal('additional_amount', 12, 2);
            $table->enum('status', ['pending', 'paid', 'success', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_upgrades');
    }
};
