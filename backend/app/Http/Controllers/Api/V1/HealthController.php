<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
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
