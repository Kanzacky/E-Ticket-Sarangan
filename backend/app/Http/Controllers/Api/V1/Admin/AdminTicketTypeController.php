<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketType;
use App\Http\Requests\Admin\TicketTypeRequest;
use Illuminate\Http\JsonResponse;

class AdminTicketTypeController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = TicketType::query();
        if ($s = $request->query('search')) $query->where('name', 'ilike', "%{$s}%");
        if ($perPage = $request->query('per_page')) {
            $p = $query->latest()->paginate((int)$perPage);
            return response()->json(['success'=>true,'message'=>'Daftar jenis tiket berhasil diambil','data'=>$p->items(),'meta'=>['current_page'=>$p->currentPage(),'last_page'=>$p->lastPage(),'per_page'=>$p->perPage(),'total'=>$p->total()]]);
        }
        $ticketTypes = $query->latest()->get(['id', 'name', 'description', 'price', 'quota', 'status', 'created_at']);
        return response()->json(['success' => true,'message' => 'Daftar jenis tiket berhasil diambil','data' => $ticketTypes]);
    }

    public function show(TicketType $ticketType): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail jenis tiket berhasil diambil',
            'data' => $ticketType->only(['id', 'name', 'description', 'price', 'quota', 'status']),
        ]);
    }

    public function store(TicketTypeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $ticketType = TicketType::create($validated);
        \App\Services\AuditService::log($request,'create_ticket_type',TicketType::class,$ticketType->id,null,$validated);
        return response()->json(['success'=>true,'message'=>'Jenis tiket berhasil dibuat','data'=>$ticketType->only(['id','name','description','price','quota','status'])],201);
    }

    public function update(TicketTypeRequest $request, TicketType $ticketType): JsonResponse
    {
        $validated = $request->validated();
        $old=$ticketType->toArray();
        $ticketType->update($validated);
        \App\Services\AuditService::log($request,'update_ticket_type',TicketType::class,$ticketType->id,$old,$validated);
        return response()->json(['success'=>true,'message'=>'Jenis tiket berhasil diperbarui','data'=>$ticketType->only(['id','name','description','price','quota','status'])]);
    }

    public function destroy(TicketType $ticketType): JsonResponse
    {
        $old=$ticketType->toArray();
        $ticketType->delete();
        \App\Services\AuditService::log(request(),'delete_ticket_type',TicketType::class,$old['id']??null,$old,null);
        return response()->json(['success'=>true,'message'=>'Jenis tiket berhasil dihapus']);
    }
}
