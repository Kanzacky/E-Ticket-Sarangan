<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use App\Services\GooglePlacesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAccommodations extends Command
{
    protected $signature = 'accommodations:sync
                            {--fresh : Hapus data source=google sebelum sync}
                            {--radius=5000 : Radius meter dari titik pusat}
                            {--limit=20 : Maksimal penginapan yang di-sync}
                            {--lat=-7.6700 : Latitude Telaga Sarangan}
                            {--lng=111.2160 : Longitude Telaga Sarangan}
                            {--type=lodging : Google Places type}
                            {--with-details : Ambil phone/details tambahan (butuh kuota ekstra)}';

    protected $description = 'Sinkronisasi penginapan sekitar Telaga Sarangan via Google Places API (Opsi C)';

    public function handle(GooglePlacesService $service): int
    {
        $apiKey = env('GOOGLE_PLACES_API_KEY') ?: config('services.google.places_key');
        if (! $apiKey) {
            $this->error('GOOGLE_PLACES_API_KEY belum di-set di .env / Vercel env.');
            $this->line('Isi GOOGLE_PLACES_API_KEY di backend/.env lalu jalankan lagi, atau set di Vercel Dashboard > Environment Variables.');
            return self::FAILURE;
        }

        $lat = (float) $this->option('lat');
        $lng = (float) $this->option('lng');
        $radius = (int) $this->option('radius');
        $type = (string) $this->option('type');
        $limit = (int) $this->option('limit');
        $withDetails = (bool) $this->option('with-details');

        if ($this->option('fresh')) {
            $deleted = Accommodation::where('source', 'google')->orWhereNotNull('google_place_id')->delete();
            $this->warn("Fresh: {$deleted} data google lama dihapus.");
        }

        $this->info("Sync penginapan: lat={$lat} lng={$lng} radius={$radius}m type={$type} limit={$limit}");
        $this->line("API Key: " . substr($apiKey, 0, 8) . str_repeat('*', max(0, strlen($apiKey)-8)));

        $collected = [];
        $pageToken = null;
        $page = 0;

        do {
            if ($page > 0) {
                // Google butuh delay sebelum next_page_token valid
                $this->line("Menunggu 2s untuk next_page_token...");
                sleep(2);
            }
            $page++;

            try {
                $data = $service->nearbySearch($lat, $lng, $radius, $type, $apiKey, $pageToken);
            } catch (\Throwable $e) {
                $this->error("Gagal fetch page {$page}: " . $e->getMessage());
                Log::error('SyncAccommodations fetch failed', ['page' => $page, 'error' => $e->getMessage()]);
                return self::FAILURE;
            }

            $results = $data['results'] ?? [];
            $this->line("Page {$page}: " . count($results) . " hasil (status: {$data['status']})");

            foreach ($results as $place) {
                if (count($collected) >= $limit) break 2;
                $collected[] = $place;
            }

            $pageToken = $data['next_page_token'] ?? null;
            if ($pageToken && count($collected) < $limit) {
                $this->line("Ada next_page_token, lanjut page berikutnya...");
            }
        } while ($pageToken && count($collected) < $limit && $page < 3); // Google max 60 results (3 pages)

        if (empty($collected)) {
            $this->warn('Tidak ada penginapan ditemukan. Coba radius lebih besar atau cek lokasi.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan " . count($collected) . " tempat, mulai upsert...");
        $created = 0; $updated = 0; $skipped = 0;

        foreach ($collected as $place) {
            $placeId = $place['place_id'] ?? null;
            $name = $place['name'] ?? null;
            if (! $placeId || ! $name) { $skipped++; continue; }

            // Cek existing by google_place_id atau name+address
            $existing = Accommodation::where('google_place_id', $placeId)->first();
            if (! $existing) {
                $vicinity = $place['vicinity'] ?? ($place['formatted_address'] ?? '');
                $existing = Accommodation::where('name', $name)->where('address', $vicinity)->first();
            }

            $rating = isset($place['rating']) ? (float) $place['rating'] : null;
            $vicinity = $place['vicinity'] ?? 'Sekitar Telaga Sarangan, Magetan';
            $types = $place['types'] ?? [];
            $photoRef = $place['photos'][0]['photo_reference'] ?? null;
            $imageUrl = $photoRef ? $service->photoUrl($photoRef, $apiKey) : null;
            $latPlace = $place['geometry']['location']['lat'] ?? $lat;
            $lngPlace = $place['geometry']['location']['lng'] ?? $lng;

            $distanceKm = $service->calculateDistance($lat, $lng, $latPlace, $lngPlace);

            $details = null;
            $phone = null;
            $priceLevel = null;
            if ($withDetails) {
                $details = $service->getDetails($placeId, $apiKey);
                $phone = $details['formatted_phone_number'] ?? null;
                $priceLevel = $details['price_level'] ?? null;
                // jangan sleep terlalu lama, tapi hindari rate limit
                usleep(200000); // 0.2s
            }

            $price = $service->estimatePrice($rating, $priceLevel);
            $facilities = $service->mapFacilities($types);
            $description = $service->buildDescription($place);

            $payload = [
                'name' => $name,
                'description' => $description,
                'address' => $vicinity,
                'phone' => $phone ?? $existing?->phone ?? '-',
                'image_url' => $imageUrl ?? $existing?->image_url,
                'price_per_night' => $existing ? $existing->price_per_night : $price, // jangan override harga manual
                'total_rooms' => $existing?->total_rooms ?? 15,
                'available_rooms' => $existing?->available_rooms ?? 15,
                'rating' => $rating ?? $existing?->rating ?? 4.0,
                'facilities' => $existing?->facilities ?? $facilities,
                'is_active' => true,
                'google_place_id' => $placeId,
                'latitude' => $latPlace,
                'longitude' => $lngPlace,
                'distance_km' => $distanceKm,
                'source' => 'google',
            ];

            if ($existing) {
                // Update hanya field yang aman, jangan timpa harga manual & available_rooms jika sudah dipesan
                $existing->update([
                    'description' => $payload['description'],
                    'address' => $payload['address'],
                    'rating' => $payload['rating'],
                    'image_url' => $payload['image_url'] ?? $existing->image_url,
                    'google_place_id' => $placeId,
                    'latitude' => $latPlace,
                    'longitude' => $lngPlace,
                    'distance_km' => $distanceKm,
                    'source' => 'google',
                    // phone hanya update jika ada dari details
                    'phone' => $phone ?? $existing->phone,
                ]);
                $updated++;
                $this->line("  Updated: {$name} ({$placeId}) distance {$distanceKm}km");
            } else {
                Accommodation::create($payload);
                $created++;
                $this->line("  Created: {$name} ({$placeId}) rating {$rating} price {$price} distance {$distanceKm}km");
            }
        }

        $this->info("Selesai: {$created} baru, {$updated} diupdate, {$skipped} skip. Total: " . Accommodation::count());
        $this->line("Catatan: harga estimasi berdasar rating/price_level. Edit manual di /admin/accommodations untuk harga/kamar akurat.");
        return self::SUCCESS;
    }
}
