<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * GET /api/health — status aplikasi + koneksi database.
     *
     * DB check di-cache 60 detik agar wake-up Railway lebih cepat:
     * hit pertama tetap buka koneksi DB, hit berikutnya dalam 60s langsung return cache.
     */
    public function __invoke(): JsonResponse
    {
        $databaseStatus = $this->databaseStatus();

        return response()->json([
            'success' => true,
            'message' => 'E-Ticket Sarangan API is running',
            'data' => [
                'status'   => 'ok',
                'app'      => config('app.name'),
                'version'  => config('app.version') ?? 'v1',
                'database' => $databaseStatus,
            ],
        ])->withHeaders([
            // Health tidak boleh di-cache browser/proxy (selalu fresh)
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * GET / — root endpoint ringan tanpa middleware berat.
     */
    public function root(): JsonResponse
    {
        return response()->json([
            'status'   => 'ok',
            'message'  => 'e-Ticket Sarangan API',
            'database' => $this->databaseStatus(),
        ]);
    }

    /**
     * GET /api/health/database — status koneksi database.
     */
    public function database(): JsonResponse
    {
        $connected = $this->databaseStatus() === 'connected';

        return response()->json([
            'success'  => $connected,
            'database' => $connected ? 'connected' : 'disconnected',
        ], $connected ? 200 : 503);
    }

    /**
     * Cache koneksi DB selama 60 detik.
     * Railway cold start: hit pertama (~200ms), hit berikutnya instant.
     */
    private function databaseStatus(): string
    {
        return Cache::remember('health_db_status', 60, function () {
            try {
                DB::connection()->getPdo();
                return 'connected';
            } catch (\Throwable) {
                return 'disconnected';
            }
        });
    }
}
