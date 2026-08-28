<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketUpgrade;
use Illuminate\Http\JsonResponse;

class AdminTicketUpgradeController extends Controller
{
    public function index(): JsonResponse
    {
        $upgrades = TicketUpgrade::with(['ticket', 'oldCategory', 'newCategory'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data upgrade tiket berhasil diambil',
            'data' => $upgrades,
        ]);
    }
}
