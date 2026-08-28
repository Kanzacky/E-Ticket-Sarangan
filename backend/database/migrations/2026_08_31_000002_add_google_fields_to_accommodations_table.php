<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->string('google_place_id')->nullable()->unique()->after('is_active');
            $table->decimal('latitude', 10, 7)->nullable()->after('google_place_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('source')->default('manual')->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn(['google_place_id', 'latitude', 'longitude', 'source']);
        });
    }
};
