<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditService
{
    public static function log(Request $request, string $action, ?string $modelType = null, ?int $modelId = null, ?array $old = null, ?array $new = null): void
    {
        try {
            AuditLog::create([
                'user_id' => $request->user()?->id,
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'old_values' => $old,
                'new_values' => $new,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Audit log failed: '.$e->getMessage());
        }
    }
}
