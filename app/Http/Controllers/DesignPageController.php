<?php

namespace App\Http\Controllers;

use App\Models\Design;
use App\Models\DesignPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesignPageController extends Controller
{
    /**
     * Listar todas las páginas de un diseño
     */
    public function index($designId)
    {
        $design = Design::owned()->findOrFail($designId);
        $pages = $design->pages()->orderBy('order')->get();
        
        return response()->json($pages);
    }

    /**
     * Crear una nueva página en un diseño
     */
    public function store(Request $request, $designId)
    {
        $design = Design::owned()->findOrFail($designId);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'canvas_type' => 'required|in:vertical,horizontal,template',
            'canvas_config' => 'required|array',
            'canvas_images' => 'nullable|array',
            'texts' => 'nullable|array',
            'thumbnail' => 'nullable|string',
        ]);

        // Obtener el último orden
        $lastOrder = $design->pages()->max('order') ?? 0;

        $page = $design->pages()->create([
            'name' => $validated['name'] ?? 'Página ' . ($lastOrder + 1),
            'order' => $lastOrder + 1,
            'canvas_type' => $validated['canvas_type'],
            'canvas_config' => $validated['canvas_config'],
            'canvas_images' => $validated['canvas_images'] ?? [],
            'texts' => $validated['texts'] ?? [],
        ]);

        // Guardar thumbnail si existe
        if (!empty($validated['thumbnail'])) {
            $this->saveThumbnail($page, $validated['thumbnail']);
        }

        // Actualizar fecha de edición del diseño
        $design->touch('last_edited_at');

        return response()->json([
            'message' => 'Página creada correctamente',
            'page' => $page,
        ], 201);
    }

    /**
     * Obtener una página específica
     */
    public function show($designId, $pageId)
    {
        $design = Design::owned()->findOrFail($designId);
        $page = $design->pages()->findOrFail($pageId);
        
        return response()->json($page);
    }

    /**
     * Actualizar una página
     */
    public function update(Request $request, $designId, $pageId)
    {
        $design = Design::owned()->findOrFail($designId);
        $page = $design->pages()->findOrFail($pageId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'canvas_type' => 'sometimes|in:vertical,horizontal,template',
            'canvas_config' => 'sometimes|array',
            'canvas_images' => 'nullable|array',
            'texts' => 'nullable|array',
            'thumbnail' => 'nullable|string',
        ]);

        $updateData = array_filter($validated, fn($key) => $key !== 'thumbnail', ARRAY_FILTER_USE_KEY);
        $page->update($updateData);

        // Actualizar thumbnail si existe
        if (!empty($validated['thumbnail'])) {
            if ($page->thumbnail_path) {
                Storage::disk('public')->delete($page->thumbnail_path);
            }
            $this->saveThumbnail($page, $validated['thumbnail']);
        }

        // Actualizar fecha de edición del diseño
        $design->touch('last_edited_at');

        return response()->json([
            'message' => 'Página actualizada correctamente',
            'page' => $page->fresh(),
        ]);
    }

    /**
     * Eliminar una página
     */
    public function destroy($designId, $pageId)
    {
        $design = Design::owned()->findOrFail($designId);
        $page = $design->pages()->findOrFail($pageId);

        // No permitir eliminar si es la única página
        if ($design->pages()->count() <= 1) {
            return response()->json([
                'message' => 'No puedes eliminar la única página del diseño',
            ], 422);
        }

        // Eliminar thumbnail
        if ($page->thumbnail_path) {
            Storage::disk('public')->delete($page->thumbnail_path);
        }

        $deletedOrder = $page->order;
        $page->delete();

        // Reordenar las páginas restantes
        $design->pages()->where('order', '>', $deletedOrder)
            ->decrement('order');

        return response()->json([
            'message' => 'Página eliminada correctamente',
        ]);
    }

    /**
     * Duplicar una página
     */
    public function duplicate($designId, $pageId)
    {
        $design = Design::owned()->findOrFail($designId);
        $original = $design->pages()->findOrFail($pageId);

        $lastOrder = $design->pages()->max('order');

        $duplicate = $original->replicate();
        $duplicate->name = $original->name . ' (Copia)';
        $duplicate->order = $lastOrder + 1;
        $duplicate->save();

        // Copiar thumbnail si existe
        if ($original->thumbnail_path && Storage::disk('public')->exists($original->thumbnail_path)) {
            $newPath = 'designs/thumbnails/page_' . $duplicate->id . '_' . time() . '.png';
            Storage::disk('public')->copy($original->thumbnail_path, $newPath);
            $duplicate->update(['thumbnail_path' => $newPath]);
        }

        return response()->json([
            'message' => 'Página duplicada correctamente',
            'page' => $duplicate,
        ], 201);
    }

    /**
     * Reordenar páginas
     */
    public function reorder(Request $request, $designId, $pageId)
    {
        $design = Design::owned()->findOrFail($designId);
        $page = $design->pages()->findOrFail($pageId);

        $validated = $request->validate([
            'new_order' => 'required|integer|min:1',
        ]);

        $oldOrder = $page->order;
        $newOrder = $validated['new_order'];

        if ($oldOrder === $newOrder) {
            return response()->json(['message' => 'Sin cambios']);
        }

        // Mover otras páginas
        if ($newOrder < $oldOrder) {
            // Mover hacia arriba
            $design->pages()
                ->where('order', '>=', $newOrder)
                ->where('order', '<', $oldOrder)
                ->increment('order');
        } else {
            // Mover hacia abajo
            $design->pages()
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->decrement('order');
        }

        $page->update(['order' => $newOrder]);

        return response()->json([
            'message' => 'Página reordenada correctamente',
            'pages' => $design->pages()->orderBy('order')->get(),
        ]);
    }

    /**
     * Guardar thumbnail de la página
     */
    private function saveThumbnail(DesignPage $page, string $base64Image)
    {
        $image = str_replace('data:image/png;base64,', '', $base64Image);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        $path = 'designs/thumbnails/page_' . $page->id . '_' . time() . '.png';
        Storage::disk('public')->put($path, $imageData);

        $page->update(['thumbnail_path' => $path]);
    }
}
