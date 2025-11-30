<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Screenshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controlador de Screenshots
 * Maneja la subida y gestión de capturas de pantalla
 */
class ScreenshotController extends Controller
{
    /**
     * Lista screenshots de un proyecto
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        // Verificar que el proyecto pertenece al usuario
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $screenshots = $project->screenshots()
            ->with('renderedImages')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($screenshots);
    }

    /**
     * Sube uno o varios screenshots
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        // Verificar que el proyecto pertenece al usuario
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $request->validate([
            'screenshots' => 'required|array|min:1|max:10',
            'screenshots.*' => 'required|image|mimes:jpeg,png,webp|max:10240',
        ]);

        $uploaded = [];

        foreach ($request->file('screenshots') as $file) {
            // Obtener dimensiones de la imagen
            $imageInfo = getimagesize($file->getPathname());
            $width = $imageInfo[0] ?? 0;
            $height = $imageInfo[1] ?? 0;

            // Detectar orientación
            $orientation = Screenshot::detectOrientation($width, $height);

            // Determinar extensión basada en el tipo de imagen real (no en la extensión del archivo)
            $mimeTypeToExtension = [
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
            ];
            $imageType = $imageInfo[2] ?? null;
            $extension = $mimeTypeToExtension[$imageType] ?? 'png';

            // Generar nombre único y guardar
            $filename = Str::uuid().'.'.$extension;
            $path = $file->storeAs("screenshots/{$project->id}", $filename, 'public');

            // Crear registro en base de datos
            $screenshot = $project->screenshots()->create([
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'orientation' => $orientation,
                'width' => $width,
                'height' => $height,
                'file_size' => $file->getSize(),
            ]);

            $uploaded[] = $screenshot;
        }

        return response()->json([
            'message' => count($uploaded).' screenshot(s) subido(s) exitosamente.',
            'screenshots' => $uploaded,
        ], 201);
    }

    /**
     * Muestra un screenshot específico
     */
    public function show(Request $request, Screenshot $screenshot): JsonResponse
    {
        // Verificar que el screenshot pertenece al usuario
        if ($screenshot->project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $screenshot->load('renderedImages.template');

        return response()->json($screenshot);
    }

    /**
     * Elimina un screenshot (soft delete)
     */
    public function destroy(Request $request, Screenshot $screenshot): JsonResponse
    {
        // Verificar que el screenshot pertenece al usuario
        if ($screenshot->project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Eliminar archivo físico
        if (Storage::disk('public')->exists($screenshot->stored_path)) {
            Storage::disk('public')->delete($screenshot->stored_path);
        }

        $screenshot->delete();

        return response()->json([
            'message' => 'Screenshot eliminado exitosamente.',
        ]);
    }
}
