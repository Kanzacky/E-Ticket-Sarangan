<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with('user:id,name,email,role');
        if ($s = $request->query('search')) {
            $query->where(function($q) use ($s){
                $q->where('action','ilike',"%{$s}%")->orWhere('model_type','ilike',"%{$s}%");
            });
        }
        if ($perPage = $request->query('per_page')) {
            $p = $query->latest()->paginate((int)$perPage);
            return response()->json(['success'=>true,'message'=>'Audit logs berhasil diambil','data'=>$p->items(),'meta'=>['current_page'=>$p->currentPage(),'last_page'=>$p->lastPage(),'per_page'=>$p->perPage(),'total'=>$p->total()]]);
        }
        $logs = $query->latest()->limit(100)->get();
        return response()->json(['success'=>true,'message'=>'Audit logs berhasil diambil','data'=>$logs]);
    }

    public function store(Request $request): JsonResponse
    {
        // manual log for testing, not used in auto flow
        $validated = $request->validate([
            'action' => 'required|string|max:255',
            'model_type' => 'nullable|string',
            'model_id' => 'nullable|integer',
        ]);

        $log = AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $validated['action'],
            'model_type' => $validated['model_type'] ?? null,
            'model_id' => $validated['model_id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true, 'data' => $log], 201);
    }
}
