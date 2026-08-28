<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Http\Requests\Admin\AccommodationRequest;
use Illuminate\Http\JsonResponse;

class AdminAccommodationController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Accommodation::query();
        if ($s=$request->query('search')) $query->where('name','ilike',"%{$s}%");
        if ($perPage=$request->query('per_page')) {
            $p=$query->latest()->paginate((int)$perPage);
            return response()->json(['success'=>true,'message'=>'Daftar penginapan berhasil diambil','data'=>$p->items(),'meta'=>['current_page'=>$p->currentPage(),'last_page'=>$p->lastPage(),'per_page'=>$p->perPage(),'total'=>$p->total()]]);
        }
        $accommodations = $query->latest()->get();
        return response()->json(['success'=>true,'message'=>'Daftar penginapan berhasil diambil','data'=>$accommodations]);
    }

    public function show($id): JsonResponse
    {
        $accommodation = Accommodation::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Detail penginapan berhasil diambil',
            'data' => $accommodation,
        ]);
    }

    private function handleImageUpload($request, array &$validated): void
    {
        if ($request->hasFile('image')) {
            $disk = env('FILESYSTEM_DISK', 'public');
            // fallback to public if s3 not configured
            if ($disk === 's3' && !env('AWS_BUCKET')) $disk = 'public';
            $path = $request->file('image')->store('accommodations', $disk);
            try {
                $validated['image_url'] = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
            } catch (\Throwable $e) {
                $validated['image_url'] = $path;
            }
            unset($validated['image']);
        } elseif (isset($validated['image'])) {
            unset($validated['image']);
        }
    }

    public function store(AccommodationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        if (!isset($validated['facilities'])) {
            $validated['facilities'] = [];
        }
        $this->handleImageUpload($request, $validated);

        $accommodation = Accommodation::create($validated);
        try {
            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'create_accommodation',
                'model_type' => Accommodation::class,
                'model_id' => $accommodation->id,
                'new_values' => $validated,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Penginapan berhasil dibuat',
            'data' => $accommodation,
        ], 201);
    }

    public function update(AccommodationRequest $request, $id): JsonResponse
    {
        $accommodation = Accommodation::findOrFail($id);
        $validated = $request->validated();
        
        if (!isset($validated['facilities'])) {
            $validated['facilities'] = [];
        }
        $old = $accommodation->toArray();
        $this->handleImageUpload($request, $validated);

        $accommodation->update($validated);
        try {
            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'update_accommodation',
                'model_type' => Accommodation::class,
                'model_id' => $accommodation->id,
                'old_values' => $old,
                'new_values' => $validated,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Penginapan berhasil diperbarui',
            'data' => $accommodation,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $accommodation = Accommodation::findOrFail($id);
        $old = $accommodation->toArray();
        $accommodation->delete();
        try {
            \App\Models\AuditLog::create([
                'user_id' => request()->user()->id,
                'action' => 'delete_accommodation',
                'model_type' => Accommodation::class,
                'model_id' => $id,
                'old_values' => $old,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Penginapan berhasil dihapus',
        ]);
    }
}
