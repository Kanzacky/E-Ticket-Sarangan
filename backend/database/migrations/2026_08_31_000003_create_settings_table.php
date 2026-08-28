<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'site_name' => 'e-Ticket Telaga Sarangan',
            'site_description' => 'Pesan tiket wisata Telaga Sarangan secara online dengan mudah dan cepat.',
            'contact_email' => 'info@sarangan.com',
            'contact_phone' => '+62 811-1234-5678',
            'address' => 'Jl. Raya Telaga Sarangan, Magetan, Jawa Timur',
            'operational_hours' => 'Senin - Minggu: 07:00 - 17:00 WIB',
            'payment_gateway' => 'production',
            'tax_rate' => '11',
        ];
        foreach ($defaults as $k => $v) {
            DB::table('settings')->insert(['key' => $k, 'value' => $v, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
