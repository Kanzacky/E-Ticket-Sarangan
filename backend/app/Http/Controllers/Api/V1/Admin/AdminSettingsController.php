<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = DB::table('settings')->get();
        $data = [];
        foreach ($rows as $r) $data[$r->key] = $r->value;

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'operational_hours' => 'nullable|string|max:255',
            'payment_gateway' => 'nullable|in:sandbox,production',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        foreach ($validated as $k => $v) {
            DB::table('settings')->updateOrInsert(['key' => $k], ['value' => (string) $v, 'updated_at' => now()]);
        }

        $rows = DB::table('settings')->get();
        $data = [];
        foreach ($rows as $r) $data[$r->key] = $r->value;

        // audit log
        try {
            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'update_settings',
                'model_type' => 'settings',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => $validated,
            ]);
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'message' => 'Pengaturan berhasil disimpan', 'data' => $data]);
    }
}
