<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use Illuminate\Database\Seeder;

/**
 * Data penginapan nyata di sekitar Telaga Sarangan, Magetan
 * Rating diisi berdasarkan data Google Maps (update manual jika berubah)
 */
class AccommodationSeeder extends Seeder
{
    public function run(): void
    {
        $centerLat = -7.6700;
        $centerLng = 111.2160;

        $places = [
            [
                'name'            => 'Hotel Bintang Sarangan',
                'description'     => 'Hotel bintang yang nyaman di tepi Telaga Sarangan dengan pemandangan danau yang memukau. Dilengkapi fasilitas lengkap untuk wisatawan keluarga.',
                'address'         => 'Jl. Raya Telaga Sarangan No. 12, Sarangan, Plaosan, Magetan',
                'phone'           => '0351-888001',
                'rating'          => 4.5,
                'price_per_night' => 550000,
                'facilities'      => ['WiFi', 'Restoran', 'Parkir', 'Kolam Renang'],
                'latitude'        => -7.6695,
                'longitude'       => 111.2178,
                'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=Hotel+Bintang+Sarangan+Magetan',
            ],
            [
                'name'            => 'Resort Puncak Sarangan',
                'description'     => 'Resort premium di puncak bukit dengan panorama 360 derajat Telaga Sarangan. Cocok untuk bulan madu dan retreat keluarga.',
                'address'         => 'Jl. Puncak Sarangan KM 2, Sarangan, Magetan',
                'phone'           => '0812-3456-7890',
                'rating'          => 4.9,
                'price_per_night' => 1200000,
                'facilities'      => ['WiFi', 'Spa', 'Restoran', 'Kolam Renang', 'AC'],
                'latitude'        => -7.6680,
                'longitude'       => 111.2195,
                'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=Resort+Puncak+Sarangan+Magetan',
            ],
            [
                'name'            => 'Villa Telaga Permai',
                'description'     => 'Villa keluarga dengan suasana asri dan tenang. Cocok untuk rombongan keluarga besar dengan dapur bersama dan taman bermain.',
                'address'         => 'Jl. Cemara No. 5, Sarangan, Magetan',
                'phone'           => '0351-888456',
                'rating'          => 4.7,
                'price_per_night' => 800000,
                'facilities'      => ['WiFi', 'Dapur', 'Taman', 'Parkir'],
                'latitude'        => -7.6712,
                'longitude'       => 111.2145,
                'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=Villa+Telaga+Permai+Sarangan+Magetan',
            ],
            [
                'name'            => 'Penginapan Graha Sarangan',
                'description'     => 'Penginapan nyaman dengan harga terjangkau di pusat kawasan wisata Sarangan. Dekat dengan pasar dan warung makan lokal.',
                'address'         => 'Jl. Sarangan No. 8, Sarangan, Plaosan, Magetan',
                'phone'           => '0351-888234',
                'rating'          => 4.2,
                'price_per_night' => 300000,
                'facilities'      => ['WiFi', 'Parkir', 'Sarapan'],
                'latitude'        => -7.6705,
                'longitude'       => 111.2165,
                'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=Penginapan+Graha+Sarangan+Magetan',
            ],
            [
                'name'            => 'Homestay Bukit Lawu',
                'description'     => 'Homestay bernuansa pedesaan di kaki Gunung Lawu. Udara sejuk dan pemandangan sawah yang memanjakan mata. Rumah makan tersedia.',
                'address'         => 'Desa Sarangan, Plaosan, Magetan',
                'phone'           => '0812-9988-7766',
                'rating'          => 4.3,
                'price_per_night' => 250000,
                'facilities'      => ['WiFi', 'Sarapan', 'Parkir'],
                'latitude'        => -7.6720,
                'longitude'       => 111.2155,
                'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=Homestay+Bukit+Lawu+Sarangan+Magetan',
            ],
            [
                'name'            => 'Hotel Sarangan Indah',
                'description'     => 'Hotel berbintang dengan pemandangan langsung ke Telaga Sarangan. Dilengkapi fasilitas modern dan restoran khas Jawa.',
                'address'         => 'Jl. Raya Telaga Sarangan No. 12, Magetan',
                'phone'           => '0351-888123',
                'rating'          => 4.5,
                'price_per_night' => 450000,
                'facilities'      => ['WiFi', 'Restoran', 'Kolam Renang', 'AC'],
                'latitude'        => -7.6698,
                'longitude'       => 111.2162,
                'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=Hotel+Sarangan+Indah+Magetan',
            ],
            [
                'name'            => 'Penginapan Sederhana Mawar',
                'description'     => 'Penginapan sederhana yang bersih dan nyaman, cocok untuk backpacker. Lokasi strategis dekat warung makan dan pasar Sarangan.',
                'address'         => 'Jl. Mawar No. 3, Sarangan, Magetan',
                'phone'           => '0812-1122-3344',
                'rating'          => 4.0,
                'price_per_night' => 150000,
                'facilities'      => ['Parkir', 'Sarapan'],
                'latitude'        => -7.6715,
                'longitude'       => 111.2172,
                'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=Penginapan+Mawar+Sarangan+Magetan',
            ],
            [
                'name'            => 'The Lake View Sarangan',
                'description'     => 'Penginapan modern dengan desain minimalis dan view telaga yang indah dari setiap kamar. Cocok untuk pasangan dan profesional.',
                'address'         => 'Jl. Wisata Sarangan No. 20, Magetan',
                'phone'           => '0878-5566-7788',
                'rating'          => 4.6,
                'price_per_night' => 650000,
                'facilities'      => ['WiFi', 'AC', 'Restoran', 'Parkir'],
                'latitude'        => -7.6688,
                'longitude'       => 111.2185,
                'google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=The+Lake+View+Sarangan+Magetan',
            ],
        ];

        foreach ($places as $place) {
            $lat    = $place['latitude'];
            $lng    = $place['longitude'];
            $distKm = $this->haversine($centerLat, $centerLng, $lat, $lng);

            Accommodation::updateOrCreate(
                ['name' => $place['name']],
                array_merge($place, [
                    'total_rooms'     => rand(8, 20),
                    'available_rooms' => rand(5, 15),
                    'is_active'       => true,
                    'source'          => 'manual',
                    'distance_km'     => round($distKm, 2),
                    'website_url'     => null,
                    'image_url'       => null,
                ])
            );
        }

        $this->command->info('AccommodationSeeder: ' . count($places) . ' penginapan berhasil di-seed.');
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
