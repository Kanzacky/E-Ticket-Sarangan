<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            if (!Schema::hasColumn('accommodations', 'google_maps_url')) {
                $table->string('google_maps_url')->nullable()->after('google_place_id');
            }
            if (!Schema::hasColumn('accommodations', 'website_url')) {
                $table->string('website_url')->nullable()->after('google_maps_url');
            }
            if (!Schema::hasColumn('accommodations', 'distance_km')) {
                $table->decimal('distance_km', 8, 3)->nullable()->after('website_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumnIfExists('google_maps_url');
            $table->dropColumnIfExists('website_url');
        });
    }
};
