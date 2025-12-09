<?php

namespace App\Http\Controllers;

use App\Models\Mockup;
use Illuminate\Http\Request;

class MockupController extends Controller
{
    /**
     * Listar todos los mockups activos
     */
    public function index(Request $request)
    {
        $query = Mockup::query()->active();

        // Filtrar por categoría si se proporciona
        if ($request->has('category')) {
            $query->category($request->category);
        }

        // Ordenar por uso más popular o más reciente
        $orderBy = $request->get('order_by', 'usage_count');
        $orderDirection = $request->get('order_direction', 'desc');
        
        $mockups = $query->orderBy($orderBy, $orderDirection)->get();

        return response()->json($mockups);
    }

    /**
     * Obtener un mockup específico
     */
    public function show($id)
    {
        $mockup = Mockup::findOrFail($id);
        return response()->json($mockup);
    }

    /**
     * Incrementar el contador de uso de un mockup
     */
    public function incrementUsage($id)
    {
        $mockup = Mockup::findOrFail($id);
        $mockup->incrementUsage();

        return response()->json([
            'success' => true,
            'usage_count' => $mockup->usage_count
        ]);
    }

    /**
     * Obtener las categorías disponibles
     */
    public function categories()
    {
        $categories = Mockup::query()
            ->active()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json($categories);
    }

    // ==========================================
    // MÉTODOS DE ADMINISTRACIÓN (solo admin)
    // ==========================================

    /**
     * Listar todos los mockups (incluidos inactivos) - ADMIN
     */
    public function adminIndex(Request $request)
    {
        $query = Mockup::query();

        // Filtrar por categoría si se proporciona
        if ($request->has('category')) {
            $query->category($request->category);
        }

        // Filtrar por estado si se proporciona
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Ordenar
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        
        $mockups = $query->orderBy($orderBy, $orderDirection)->get();

        return response()->json($mockups);
    }

    /**
     * Subir un nuevo mockup - ADMIN
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
        ]);

        try {
            // Subir archivo principal
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/mockups', $filename);

            // Subir thumbnail si existe
            $thumbnailName = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail');
                $thumbnailName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
                $thumbnail->storeAs('public/mockups/thumbnails', $thumbnailName);
            }

            // Obtener dimensiones de la imagen si no se proporcionan
            $width = $request->width;
            $height = $request->height;
            
            if (!$width || !$height) {
                $imagePath = storage_path('app/public/mockups/' . $filename);
                $imageInfo = getimagesize($imagePath);
                $width = $width ?: $imageInfo[0];
                $height = $height ?: $imageInfo[1];
            }

            // Crear registro en la base de datos
            $mockup = Mockup::create([
                'name' => $request->name,
                'category' => $request->category,
                'filename' => $filename,
                'thumbnail' => $thumbnailName,
                'width' => $width,
                'height' => $height,
                'is_active' => true,
            ]);

            return response()->json([
                'message' => 'Mockup creado exitosamente',
                'mockup' => $mockup
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al subir mockup',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar información de un mockup - ADMIN
     */
    public function update(Request $request, $id)
    {
        $mockup = Mockup::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:255',
            'width' => 'sometimes|nullable|integer|min:1',
            'height' => 'sometimes|nullable|integer|min:1',
        ]);

        $mockup->update($request->only(['name', 'category', 'width', 'height']));

        return response()->json([
            'message' => 'Mockup actualizado exitosamente',
            'mockup' => $mockup
        ]);
    }

    /**
     * Activar/Desactivar un mockup (soft delete) - ADMIN
     */
    public function toggleActive($id)
    {
        $mockup = Mockup::findOrFail($id);
        $mockup->is_active = !$mockup->is_active;
        $mockup->save();

        $status = $mockup->is_active ? 'activado' : 'desactivado';

        return response()->json([
            'message' => "Mockup {$status} exitosamente",
            'mockup' => $mockup
        ]);
    }

    /**
     * Eliminar permanentemente un mockup y sus archivos - ADMIN
     */
    public function destroy($id)
    {
        $mockup = Mockup::findOrFail($id);

        try {
            // Eliminar archivo principal
            $mainFilePath = storage_path('app/public/mockups/' . $mockup->filename);
            if (file_exists($mainFilePath)) {
                unlink($mainFilePath);
            }

            // Eliminar thumbnail si existe
            if ($mockup->thumbnail) {
                $thumbnailPath = storage_path('app/public/mockups/thumbnails/' . $mockup->thumbnail);
                if (file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                }
            }

            // Eliminar registro de base de datos
            $mockup->delete();

            return response()->json([
                'message' => 'Mockup eliminado permanentemente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar mockup',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de mockups - ADMIN
     */
    public function stats()
    {
        return response()->json([
            'total' => Mockup::count(),
            'active' => Mockup::where('is_active', true)->count(),
            'inactive' => Mockup::where('is_active', false)->count(),
            'by_category' => Mockup::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->get(),
            'most_used' => Mockup::orderBy('usage_count', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'category', 'usage_count']),
        ]);
    }
}
