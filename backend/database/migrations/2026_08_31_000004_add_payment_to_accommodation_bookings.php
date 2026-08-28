<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodation_bookings', function (Blueprint $table) {
            $table->string('payment_id')->nullable()->after('total_price');
            $table->text('payment_url')->nullable()->after('payment_id');
            $table->timestamp('payment_expires_at')->nullable()->after('payment_url');
        });
    }

    public function down(): void
    {
        Schema::table('accommodation_bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_id', 'payment_url', 'payment_expires_at']);
        });
    }
};
