<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use App\Http\Requests\Admin\TicketCategoryRequest;
use Illuminate\Http\JsonResponse;

class AdminTicketCategoryController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = TicketCategory::query();
        if ($s=$request->query('search')) $query->where('name','ilike',"%{$s}%");
        if ($perPage=$request->query('per_page')) {
            $p=$query->orderBy('id','desc')->paginate((int)$perPage);
            return response()->json(['success'=>true,'message'=>'Daftar paket wisata berhasil diambil','data'=>$p->items(),'meta'=>['current_page'=>$p->currentPage(),'last_page'=>$p->lastPage(),'per_page'=>$p->perPage(),'total'=>$p->total()]]);
        }
        $categories = $query->orderBy('id', 'desc')->get();
        return response()->json(['success'=>true,'message'=>'Daftar paket wisata berhasil diambil','data'=>$categories]);
    }

    public function store(TicketCategoryRequest $request): JsonResponse
    {
        $validated=$request->validated();
        $category = TicketCategory::create($validated);
        \App\Services\AuditService::log($request,'create_ticket_category',TicketCategory::class,$category->id,null,$validated);
        return response()->json(['success'=>true,'message'=>'Paket wisata berhasil ditambahkan','data'=>$category],201);
    }

    public function show($id): JsonResponse
    {
        $category = TicketCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Paket wisata tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail paket wisata berhasil diambil',
            'data' => $category,
        ]);
    }

    public function update(TicketCategoryRequest $request, $id): JsonResponse
    {
        $category = TicketCategory::find($id);
        if (!$category) return response()->json(['success'=>false,'message'=>'Paket wisata tidak ditemukan'],404);
        $old=$category->toArray();
        $validated=$request->validated();
        $category->update($validated);
        \App\Services\AuditService::log($request,'update_ticket_category',TicketCategory::class,$category->id,$old,$validated);
        return response()->json(['success'=>true,'message'=>'Paket wisata berhasil diupdate','data'=>$category]);
    }

    public function destroy($id): JsonResponse
    {
        $category = TicketCategory::find($id);
        if (!$category) return response()->json(['success'=>false,'message'=>'Paket wisata tidak ditemukan'],404);
        $old=$category->toArray();
        $category->delete();
        \App\Services\AuditService::log(request(),'delete_ticket_category',TicketCategory::class,$old['id']??null,$old,null);
        return response()->json(['success'=>true,'message'=>'Paket wisata berhasil dihapus']);
    }
}
