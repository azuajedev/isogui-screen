<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesignController extends Controller
{
    /**
     * Listar todos los diseños del usuario autenticado
     */
    public function index()
    {
        $designs = Design::owned()
            ->recentlyEdited()
            ->select('id', 'name', 'description', 'canvas_type', 'thumbnail_path', 'last_edited_at', 'created_at')
            ->paginate(20);

        return response()->json($designs);
    }

    /**
     * Obtener un diseño específico
     */
    public function show($id)
    {
        $design = Design::with('pages')->owned()->findOrFail($id);
        return response()->json($design);
    }

    /**
     * Guardar un nuevo diseño
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'canvas_type' => 'required|in:vertical,horizontal,template',
            'canvas_config' => 'required|array',
            'canvas_images' => 'nullable|array',
            'gallery_images' => 'nullable|array',
            'texts' => 'nullable|array',
            'thumbnail' => 'nullable|string', // Base64 del thumbnail
            'device_configs' => 'nullable|array', // Estructura multi-dispositivo
            'current_device_key' => 'nullable|string', // Dispositivo actual
        ]);

        $design = Design::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'canvas_type' => $validated['canvas_type'],
            'canvas_config' => $validated['canvas_config'],
            'canvas_images' => $validated['canvas_images'] ?? [],
            'gallery_images' => $validated['gallery_images'] ?? [],
            'texts' => $validated['texts'] ?? [],
            'last_edited_at' => now(),
            'device_configs' => $validated['device_configs'] ?? null,
            'current_device_key' => $validated['current_device_key'] ?? null,
        ]);

        // Crear primera página automáticamente
        $design->pages()->create([
            'name' => 'Página 1',
            'order' => 1,
            'canvas_type' => $validated['canvas_type'],
            'canvas_config' => $validated['canvas_config'],
            'canvas_images' => $validated['canvas_images'] ?? [],
            'texts' => $validated['texts'] ?? [],
        ]);

        // Guardar thumbnail si existe
        if (!empty($validated['thumbnail'])) {
            $this->saveThumbnail($design, $validated['thumbnail']);
        }

        return response()->json([
            'message' => 'Diseño guardado correctamente',
            'design' => $design->load('pages'),
        ], 201);
    }

    /**
     * Actualizar un diseño existente
     */
    public function update(Request $request, $id)
    {
        $design = Design::owned()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'canvas_type' => 'sometimes|in:vertical,horizontal,template',
            'canvas_config' => 'sometimes|array',
            'canvas_images' => 'nullable|array',
            'gallery_images' => 'nullable|array',
            'texts' => 'nullable|array',
            'thumbnail' => 'nullable|string',
            'device_configs' => 'nullable|array', // Estructura multi-dispositivo
            'current_device_key' => 'nullable|string', // Dispositivo actual
        ]);

        $updateData = array_filter($validated, fn($key) => $key !== 'thumbnail', ARRAY_FILTER_USE_KEY);
        $updateData['last_edited_at'] = now();

        $design->update($updateData);

        // Actualizar thumbnail si existe
        if (!empty($validated['thumbnail'])) {
            // Eliminar thumbnail anterior
            if ($design->thumbnail_path) {
                Storage::disk('public')->delete($design->thumbnail_path);
            }
            $this->saveThumbnail($design, $validated['thumbnail']);
        }

        return response()->json([
            'message' => 'Diseño actualizado correctamente',
            'design' => $design->fresh(),
        ]);
    }

    /**
     * Eliminar un diseño
     */
    public function destroy($id)
    {
        $design = Design::owned()->findOrFail($id);

        // Eliminar thumbnail
        if ($design->thumbnail_path) {
            Storage::disk('public')->delete($design->thumbnail_path);
        }

        // Eliminar todas las imágenes del diseño en storage
        $userId = auth()->id();
        $designPath = "{$userId}/{$id}";
        $disk = 'designs'; // Usar el disco 'designs' configurado
        
        if (Storage::disk($disk)->exists($designPath)) {
            Storage::disk($disk)->deleteDirectory($designPath);
        }

        $design->delete();

        return response()->json([
            'message' => 'Diseño eliminado correctamente',
        ]);
    }

    /**
     * Duplicar un diseño
     */
    public function duplicate($id)
    {
        $original = Design::owned()->findOrFail($id);

        $duplicate = $original->replicate();
        $duplicate->name = $original->name . ' (Copia)';
        $duplicate->last_edited_at = now();
        $duplicate->save();

        // Copiar thumbnail si existe
        if ($original->thumbnail_path && Storage::disk('public')->exists($original->thumbnail_path)) {
            $newPath = 'designs/thumbnails/' . $duplicate->id . '_' . time() . '.png';
            Storage::disk('public')->copy($original->thumbnail_path, $newPath);
            $duplicate->update(['thumbnail_path' => $newPath]);
        }

        return response()->json([
            'message' => 'Diseño duplicado correctamente',
            'design' => $duplicate,
        ], 201);
    }

    /**
     * Guardar thumbnail del diseño
     */
    private function saveThumbnail(Design $design, string $base64Image)
    {
        // Remover el prefijo "data:image/png;base64,"
        $image = str_replace('data:image/png;base64,', '', $base64Image);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        $path = 'designs/thumbnails/' . $design->id . '_' . time() . '.png';
        Storage::disk('public')->put($path, $imageData);

        $design->update(['thumbnail_path' => $path]);
    }
}
