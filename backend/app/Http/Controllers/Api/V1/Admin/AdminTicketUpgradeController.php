<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AdminTicketUpgradeController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        // Legacy feature: ticket_upgrades table removed for clean deploy
        // Return empty list with info
        return response()->json([
            'success' => true,
            'message' => 'Fitur upgrade tiket legacy - tidak ada data (orders flow tidak pakai ticket_upgrades)',
            'data' => [],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 10, 'total' => 0],
        ]);
    }
}
