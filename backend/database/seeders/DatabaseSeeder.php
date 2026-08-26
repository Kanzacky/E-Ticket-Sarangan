<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default users
        User::firstOrCreate(
            ['email' => 'admin@sarangan.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081111111111',
            ]
        );

        User::firstOrCreate(
            ['email' => 'petugas@sarangan.test'],
            [
                'name' => 'Petugas Loket',
                'password' => Hash::make('password'),
                'role' => 'petugas',
                'phone' => '082222222222',
            ]
        );

        User::firstOrCreate(
            ['email' => 'wisatawan@sarangan.test'],
            [
                'name' => 'Wisatawan Umum',
                'password' => Hash::make('password'),
                'role' => 'wisatawan',
                'phone' => '083333333333',
            ]
        );

        // Default Ticket Types (Fase Pemesanan)
        TicketType::firstOrCreate(
            ['name' => 'Tiket Dewasa'],
            [
                'description' => 'Tiket masuk untuk pengunjung dewasa (> 12 tahun)',
                'price' => 20000,
                'quota' => 500,
                'status' => 'ACTIVE',
            ]
        );

        TicketType::firstOrCreate(
            ['name' => 'Tiket Anak'],
            [
                'description' => 'Tiket masuk untuk anak-anak (usia 3 - 12 tahun)',
                'price' => 10000,
                'quota' => 300,
                'status' => 'ACTIVE',
            ]
        );

        TicketType::firstOrCreate(
            ['name' => 'Tiket Mancanegara'],
            [
                'description' => 'Tiket masuk untuk wisatawan mancanegara / WNA',
                'price' => 50000,
                'quota' => 100,
                'status' => 'ACTIVE',
            ]
        );

        // Default Ticket Categories
        TicketCategory::firstOrCreate(
            ['name' => 'Dewasa'],
            [
                'description' => 'Tiket untuk pengunjung dewasa (usia > 12 tahun)',
                'price' => 20000,
                'min_age' => 13,
                'max_age' => null,
                'is_active' => true,
            ]
        );

        TicketCategory::firstOrCreate(
            ['name' => 'Anak-anak'],
            [
                'description' => 'Tiket untuk anak-anak (usia 3 - 12 tahun)',
                'price' => 10000,
                'min_age' => 3,
                'max_age' => 12,
                'is_active' => true,
            ]
        );

        TicketCategory::firstOrCreate(
            ['name' => 'Mancanegara'],
            [
                'description' => 'Tiket untuk wisatawan asing',
                'price' => 50000,
                'min_age' => null,
                'max_age' => null,
                'is_active' => true,
            ]
        );

        // Accommodations
        $this->call(AccommodationSeeder::class);
    }
}
