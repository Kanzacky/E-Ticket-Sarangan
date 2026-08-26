<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('image_url')->nullable();
            $table->integer('price_per_night')->default(0);
            $table->integer('total_rooms')->default(1);
            $table->integer('available_rooms')->default(1);
            $table->decimal('rating', 2, 1)->default(0);
            $table->json('facilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('accommodation_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accommodation_id')->constrained()->cascadeOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('rooms')->default(1);
            $table->integer('guests')->default(1);
            $table->integer('total_price')->default(0);
            $table->string('guest_name');
            $table->string('guest_phone');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_bookings');
        Schema::dropIfExists('accommodations');
    }
};
