<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use Illuminate\Database\Seeder;

class AccommodationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Hotel Sarangan Indah',
                'description' => 'Hotel berbintang dengan pemandangan langsung ke Telaga Sarangan. Dilengkapi fasilitas restoran, kolam renang, dan area parkir luas.',
                'address' => 'Jl. Raya Telaga Sarangan No. 12, Magetan',
                'phone' => '0351-888123',
                'price_per_night' => 450000,
                'total_rooms' => 30,
                'available_rooms' => 12,
                'rating' => 4.5,
                'facilities' => ['WiFi', 'Restoran', 'Kolam Renang', 'Parkir', 'AC', 'TV'],
                'is_active' => true,
            ],
            [
                'name' => 'Villa Telaga Permai',
                'description' => 'Villa keluarga dengan suasana asri dan tenang. Cocok untuk rombongan keluarga besar dengan kapasitas hingga 10 orang.',
                'address' => 'Jl. Cemara No. 5, Sarangan, Magetan',
                'phone' => '0351-888456',
                'price_per_night' => 800000,
                'total_rooms' => 8,
                'available_rooms' => 3,
                'rating' => 4.7,
                'facilities' => ['WiFi', 'Dapur', 'Taman', 'Parkir', 'BBQ Area', 'Perapian'],
                'is_active' => true,
            ],
            [
                'name' => 'Penginapan Sederhana Mawar',
                'description' => 'Penginapan murah dan nyaman untuk para backpacker dan wisatawan hemat. Lokasi strategis dekat dengan pintu masuk telaga.',
                'address' => 'Jl. Telaga No. 8, Sarangan, Magetan',
                'phone' => '0351-888789',
                'price_per_night' => 150000,
                'total_rooms' => 15,
                'available_rooms' => 8,
                'rating' => 4.0,
                'facilities' => ['WiFi', 'Parkir', 'Air Panas'],
                'is_active' => true,
            ],
            [
                'name' => 'Homestay Bukit Lawu',
                'description' => 'Homestay bernuansa pegunungan dengan udara sejuk khas Sarangan. Sarapan tradisional disediakan setiap pagi.',
                'address' => 'Jl. Lawu Indah No. 3, Sarangan, Magetan',
                'phone' => '0351-888321',
                'price_per_night' => 250000,
                'total_rooms' => 10,
                'available_rooms' => 5,
                'rating' => 4.3,
                'facilities' => ['WiFi', 'Sarapan', 'Parkir', 'Taman', 'Air Panas'],
                'is_active' => true,
            ],
            [
                'name' => 'Resort Puncak Sarangan',
                'description' => 'Resort premium di puncak bukit dengan panorama 360 derajat Telaga Sarangan dan Gunung Lawu. Pengalaman menginap terbaik.',
                'address' => 'Jl. Puncak Sarangan KM 2, Magetan',
                'phone' => '0351-888999',
                'price_per_night' => 1200000,
                'total_rooms' => 20,
                'available_rooms' => 6,
                'rating' => 4.9,
                'facilities' => ['WiFi', 'Spa', 'Restoran', 'Kolam Renang', 'Gym', 'Parkir', 'Room Service', 'Laundry'],
                'is_active' => true,
            ],
        ];

        foreach ($data as $item) {
            Accommodation::create($item);
        }
    }
}
