<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * GET /api/v1/health — status aplikasi + status database.
     */
    public function __invoke(): JsonResponse
    {
        $data = [
            'status' => 'ok',
            'app' => config('app.name'),
            'version' => config('app.version') ?? 'v1',
            'database' => $this->databaseStatus(),
        ];

        return ApiResponse::success('E-Ticket Sarangan API is running', $data);
    }

    /**
     * GET /api/health — liveness check, TANPA query database.
     */
    public function app(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Backend API berjalan',
            'environment' => app()->environment(),
        ]);
    }

    /**
     * GET /api/health/database — cek koneksi Supabase PostgreSQL.
     * Tidak pernah mengekspos detail koneksi/kredensial.
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
        } catch (\Throwable) {
            return 'disconnected';
        }
    }
}
