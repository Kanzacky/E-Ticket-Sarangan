<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketUpgrade;
use Illuminate\Http\JsonResponse;

class AdminTicketUpgradeController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = TicketUpgrade::with(['ticket', 'oldCategory', 'newCategory']);
        if ($perPage = $request->query('per_page')) {
            $p = $query->latest()->paginate((int)$perPage);
            return response()->json(['success'=>true,'message'=>'Data upgrade tiket berhasil diambil','data'=>$p->items(),'meta'=>['current_page'=>$p->currentPage(),'last_page'=>$p->lastPage(),'per_page'=>$p->perPage(),'total'=>$p->total()]]);
        }
        $upgrades = $query->latest()->get();
        return response()->json(['success'=>true,'message'=>'Data upgrade tiket berhasil diambil','data'=>$upgrades]);
    }
}
