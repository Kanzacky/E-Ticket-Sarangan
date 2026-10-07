<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncAccommodations extends Command
{
    protected $signature = 'accommodations:sync
                            {--fresh : Hapus data source=overpass sebelum sync}
                            {--radius=5000 : Radius meter dari titik pusat (default 5km)}
                            {--limit=30 : Maksimal penginapan yang di-sync}
                            {--lat=-7.6700 : Latitude Telaga Sarangan}
                            {--lng=111.2160 : Longitude Telaga Sarangan}';

    protected $description = 'Sinkronisasi penginapan sekitar Telaga Sarangan via Overpass API (OpenStreetMap) — GRATIS, tanpa API key';

    // Koordinat Telaga Sarangan
    private float $centerLat = -7.6700;
    private float $centerLng = 111.2160;

    public function handle(): int
    {
        $lat    = (float) $this->option('lat');
        $lng    = (float) $this->option('lng');
        $radius = (int)   $this->option('radius');
        $limit  = (int)   $this->option('limit');

        $this->centerLat = $lat;
        $this->centerLng = $lng;

        if ($this->option('fresh')) {
            $deleted = Accommodation::where('source', 'overpass')->orWhere('source', 'google')->delete();
            $this->warn("Fresh: {$deleted} data lama dihapus.");
        }

        $this->info("Sync penginapan via Overpass API (gratis, tanpa API key)");
        $this->info("Lokasi: lat={$lat} lng={$lng} radius={$radius}m limit={$limit}");

        // Query Overpass untuk hotel, hostel, villa, guest_house, motel di sekitar Sarangan
        $query = <<<OVERPASS
[out:json][timeout:30];
(
  node["tourism"="hotel"](around:{$radius},{$lat},{$lng});
  node["tourism"="hostel"](around:{$radius},{$lat},{$lng});
  node["tourism"="guest_house"](around:{$radius},{$lat},{$lng});
  node["tourism"="motel"](around:{$radius},{$lat},{$lng});
  node["tourism"="apartment"](around:{$radius},{$lat},{$lng});
  node["tourism"="chalet"](around:{$radius},{$lat},{$lng});
  node["leisure"="resort"](around:{$radius},{$lat},{$lng});
  way["tourism"="hotel"](around:{$radius},{$lat},{$lng});
  way["tourism"="guest_house"](around:{$radius},{$lat},{$lng});
  way["tourism"="hostel"](around:{$radius},{$lat},{$lng});
  way["leisure"="resort"](around:{$radius},{$lat},{$lng});
);
out center;
OVERPASS;

        $overpassServers = [
            'https://overpass-api.de/api/interpreter',
            'https://overpass.private.coffee/api/interpreter',
            'https://overpass.kumi.systems/api/interpreter',
        ];

        $response = null;
        foreach ($overpassServers as $server) {
            $this->line("Mencoba server: {$server}");
            try {
                $resp = Http::timeout(45)
                    ->withHeaders([
                        'Accept'     => 'application/json, text/javascript, */*',
                        'User-Agent' => 'e-ticket-sarangan/1.0 (accommodation sync)',
                    ])
                    ->asForm()
                    ->post($server, ['data' => $query]);

                if ($resp->successful()) {
                    $response = $resp;
                    $this->line("Berhasil dari: {$server}");
                    break;
                }
                $this->warn("Server {$server} gagal (HTTP {$resp->status()}), mencoba server lain...");
            } catch (\Throwable $e) {
                $this->warn("Server {$server} error: " . $e->getMessage() . " — mencoba server lain...");
            }
        }

        if (!$response) {
            $this->error("Semua server Overpass API tidak dapat diakses saat ini.");
            $this->line("Tip: Coba jalankan lagi nanti, atau gunakan data seed manual dengan: php artisan db:seed --class=AccommodationSeeder");
            return self::FAILURE;
        }

        $elements = $response->json('elements', []);
        $this->line("Ditemukan " . count($elements) . " elemen dari Overpass API.");

        if (empty($elements)) {
            $this->warn("Tidak ada penginapan ditemukan. Coba radius lebih besar (--radius=10000).");
            return self::SUCCESS;
        }

        $created = 0; $updated = 0; $skipped = 0;
        $count   = 0;

        foreach ($elements as $el) {
            if ($count >= $limit) break;

            $tags  = $el['tags'] ?? [];
            $name  = $tags['name'] ?? ($tags['name:id'] ?? null);
            if (!$name) { $skipped++; continue; }

            // Koordinat
            if (isset($el['lat'])) {
                $elLat = (float) $el['lat'];
                $elLng = (float) $el['lon'];
            } elseif (isset($el['center'])) {
                $elLat = (float) $el['center']['lat'];
                $elLng = (float) $el['center']['lon'];
            } else {
                $skipped++; continue;
            }

            $osmId   = 'osm_' . $el['type'] . '_' . $el['id'];
            $address = $this->buildAddress($tags);
            $phone   = $tags['phone'] ?? ($tags['contact:phone'] ?? null);
            $stars   = isset($tags['stars']) ? (float) $tags['stars'] : null;
            $tourism = $tags['tourism'] ?? ($tags['leisure'] ?? 'hotel');
            $website = $tags['website'] ?? ($tags['contact:website'] ?? null);

            // Rating estimasi: dari bintang OSM, atau default 4.0
            $rating = $stars ? min(5.0, round($stars, 1)) : 4.0;

            // Generate Google Maps URL dari koordinat (gratis)
            $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($name . ' ' . $address);

            $distanceKm = $this->haversine($lat, $lng, $elLat, $elLng);
            $facilities = $this->guessFacilities($tags);
            $description = $this->buildDescription($name, $tourism, $tags, $distanceKm);
            $price = $this->estimatePrice($rating, $stars);

            $payload = [
                'name'            => $name,
                'description'     => $description,
                'address'         => $address,
                'phone'           => $phone ?? '-',
                'image_url'       => null,
                'price_per_night' => $price,
                'total_rooms'     => 10,
                'available_rooms' => 10,
                'rating'          => $rating,
                'facilities'      => $facilities,
                'is_active'       => true,
                'google_place_id' => $osmId,
                'google_maps_url' => $googleMapsUrl,
                'latitude'        => $elLat,
                'longitude'       => $elLng,
                'distance_km'     => round($distanceKm, 2),
                'source'          => 'overpass',
                'website_url'     => $website,
            ];

            $existing = Accommodation::where('google_place_id', $osmId)->first();
            if (!$existing) {
                $existing = Accommodation::where('name', $name)->first();
            }

            if ($existing) {
                $existing->update([
                    'description'    => $payload['description'],
                    'address'        => $payload['address'],
                    'rating'         => $payload['rating'],
                    'google_place_id'=> $osmId,
                    'google_maps_url'=> $googleMapsUrl,
                    'latitude'       => $elLat,
                    'longitude'      => $elLng,
                    'distance_km'    => round($distanceKm, 2),
                    'source'         => 'overpass',
                    'phone'          => $phone ?? $existing->phone,
                    'website_url'    => $website ?? $existing->website_url,
                ]);
                $updated++;
                $this->line("  Updated: {$name} ({$osmId}) dist {$distanceKm}km");
            } else {
                Accommodation::create($payload);
                $created++;
                $this->line("  Created: {$name} ({$osmId}) rating {$rating} dist {$distanceKm}km");
            }

            $count++;
        }

        $this->info("Selesai: {$created} baru, {$updated} diupdate, {$skipped} skip.");
        $this->info("Total penginapan: " . Accommodation::where('is_active', true)->count());
        $this->line("Tip: Edit rating & harga manual di /admin/accommodations untuk data yang lebih akurat.");
        return self::SUCCESS;
    }

    private function buildAddress(array $tags): string
    {
        $parts = array_filter([
            $tags['addr:street']  ?? null,
            $tags['addr:city']    ?? null,
            $tags['addr:suburb']  ?? null,
        ]);
        return $parts ? implode(', ', $parts) : ($tags['addr:full'] ?? 'Sekitar Telaga Sarangan, Magetan');
    }

    private function guessFacilities(array $tags): array
    {
        $facilities = [];
        if (($tags['internet_access'] ?? '') === 'wlan' || ($tags['wifi'] ?? '') === 'yes') $facilities[] = 'WiFi';
        if (($tags['swimming_pool'] ?? '') === 'yes' || ($tags['leisure'] ?? '') === 'swimming_pool') $facilities[] = 'Kolam Renang';
        if ($tags['restaurant'] ?? false) $facilities[] = 'Restoran';
        if ($tags['parking'] ?? false) $facilities[] = 'Parkir';
        if (($tags['air_conditioning'] ?? '') === 'yes') $facilities[] = 'AC';
        if (($tags['breakfast'] ?? '') === 'yes') $facilities[] = 'Sarapan';
        // Default
        if (empty($facilities)) $facilities = ['WiFi', 'Parkir'];
        return $facilities;
    }

    private function buildDescription(string $name, string $type, array $tags, float $dist): string
    {
        $typeLabel = match ($type) {
            'guest_house' => 'Guest house',
            'hostel'      => 'Hostel',
            'motel'       => 'Motel',
            'resort'      => 'Resort',
            'chalet'      => 'Vila / Chalet',
            'apartment'   => 'Apartemen / penginapan',
            default       => 'Hotel / penginapan',
        };
        $distStr = round($dist, 1);
        return "{$typeLabel} yang berlokasi ±{$distStr} km dari Telaga Sarangan, Magetan. Cocok untuk wisatawan yang ingin menikmati keindahan alam Sarangan.";
    }

    private function estimatePrice(float $rating, ?float $stars): int
    {
        if ($stars >= 4 || $rating >= 4.5) return 800000;
        if ($stars >= 3 || $rating >= 4.0) return 450000;
        if ($rating >= 3.5) return 250000;
        return 150000;
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2)**2;
        return $R * 2 * atan2(sqrt($a), sqrt(1-$a));
    }
}
