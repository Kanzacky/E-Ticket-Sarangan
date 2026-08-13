<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_visitor_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_code')->unique()->index();
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['valid', 'used', 'expired', 'cancelled'])->default('valid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
