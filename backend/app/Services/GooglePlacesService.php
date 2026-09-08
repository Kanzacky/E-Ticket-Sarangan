<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    private const NEARBY_URL = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json';
    private const DETAILS_URL = 'https://maps.googleapis.com/maps/api/place/details/json';
    private const PHOTO_URL = 'https://maps.googleapis.com/maps/api/place/photo';

    public function nearbySearch(float $lat, float $lng, int $radius, string $type, string $apiKey, ?string $pageToken = null): array
    {
        $params = [
            'location' => "{$lat},{$lng}",
            'radius' => $radius,
            'type' => $type,
            'key' => $apiKey,
        ];
        if ($pageToken) {
            $params['pagetoken'] = $pageToken;
        }

        $response = Http::timeout(15)->get(self::NEARBY_URL, $params);

        if ($response->failed()) {
            Log::warning('Google Places nearbysearch failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Google Places request failed: ' . $response->body());
        }

        $data = $response->json();

        if (! in_array($data['status'] ?? '', ['OK', 'ZERO_RESULTS'], true)) {
            $msg = $data['status'] . ': ' . ($data['error_message'] ?? 'unknown');
            Log::warning('Google Places error', $data);
            throw new \RuntimeException("Google Places error: {$msg}");
        }

        return $data;
    }

    public function getDetails(string $placeId, string $apiKey): ?array
    {
        $response = Http::timeout(10)->get(self::DETAILS_URL, [
            'place_id' => $placeId,
            'fields' => 'formatted_phone_number,formatted_address,website,price_level',
            'key' => $apiKey,
            'language' => 'id',
        ]);

        if ($response->failed()) {
            Log::warning('Google Places details failed', ['place_id' => $placeId, 'body' => $response->body()]);
            return null;
        }
        $data = $response->json();
        if (($data['status'] ?? '') !== 'OK') {
            return null;
        }
        return $data['result'] ?? null;
    }

    public function photoUrl(string $photoReference, string $apiKey, int $maxWidth = 800): string
    {
        return self::PHOTO_URL . "?maxwidth={$maxWidth}&photo_reference={$photoReference}&key={$apiKey}";
    }

    public function estimatePrice(?float $rating, ?int $priceLevel = null): int
    {
        if ($priceLevel !== null) {
            return match ($priceLevel) {
                0 => 150000,
                1 => 250000,
                2 => 450000,
                3 => 800000,
                4 => 1200000,
                default => 350000,
            };
        }
        if ($rating === null) return 350000;
        return match (true) {
            $rating >= 4.7 => 900000,
            $rating >= 4.5 => 650000,
            $rating >= 4.3 => 400000,
            $rating >= 4.0 => 280000,
            default => 180000,
        };
    }

    public function mapFacilities(array $types): array
    {
        $map = [
            'restaurant' => 'Restoran',
            'cafe' => 'Kafe',
            'spa' => 'Spa',
            'gym' => 'Gym',
            'pool' => 'Kolam Renang',
            'parking' => 'Parkir',
        ];
        $facilities = ['WiFi', 'Parkir', 'AC'];
        foreach ($types as $t) {
            if (isset($map[$t]) && ! in_array($map[$t], $facilities, true)) {
                $facilities[] = $map[$t];
            }
        }
        if (in_array('lodging', $types, true) && ! in_array('Sarapan', $facilities, true)) {
            // many lodgings around Sarangan offer breakfast
        }
        return array_values(array_unique($facilities));
    }

    public function buildDescription(array $place): string
    {
        $name = $place['name'] ?? 'Penginapan';
        $vicinity = $place['vicinity'] ?? '';
        $rating = $place['rating'] ?? null;
        $types = $place['types'] ?? [];
        $typeStr = implode(', ', array_slice($types, 0, 3));
        $desc = "{$name} di sekitar Telaga Sarangan";
        if ($vicinity) $desc .= " — {$vicinity}.";
        if ($rating) $desc .= " Rating Google {$rating}/5.";
        if ($typeStr) $desc .= " Kategori: {$typeStr}.";
        $desc .= " Data sinkronisasi otomatis Google Places, harga estimasi — silakan konfirmasi ke penginapan.";
        return $desc;
    }

    /**
     * Hitung jarak menggunakan Haversine formula (dalam km)
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 3);
    }
}
