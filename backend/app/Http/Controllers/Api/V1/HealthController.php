<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * GET /api/v1/health — status aplikasi, tanpa gagal saat DB belum siap.
     */
    public function __invoke(): JsonResponse
    {
        $databaseStatus = $this->databaseStatus();

        return response()->json([
            'success' => true,
            'message' => 'E-Ticket Sarangan API is running',
            'data' => [
                'status' => 'ok',
                'app' => config('app.name'),
                'version' => config('app.version') ?? 'v1',
                'database' => $databaseStatus,
            ],
        ]);
    }

    /**
     * GET /api/health — liveness check yang aman di serverless/Vercel.
     */
    public function app(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'API e-Ticket Sarangan aktif',
        ]);
    }

    /**
     * GET /api/health/database — status koneksi database dipisah dari health check.
     */
    public function database(): JsonResponse
    {
        $connected = $this->databaseStatus() === 'connected';

        $payload = [
            'success' => $connected,
            'database' => $connected ? 'connected' : 'disconnected',
        ];

        if (! $connected) {
            $payload['error'] = 'Database connection failed. Check DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, and DB_SSLMODE.';
        }

        return response()->json($payload, $connected ? 200 : 503);
    }

    private function databaseStatus(): string
    {
        try {
            DB::connection()->getPdo();

            return 'connected';
        } catch (\Throwable $e) {
            return 'disconnected';
        }
    }
}
