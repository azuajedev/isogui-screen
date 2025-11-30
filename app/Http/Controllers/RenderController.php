<?php

namespace App\Http\Controllers;

use App\Models\RenderedImage;
use App\Models\Screenshot;
use App\Models\Template;
use App\Services\MockupRenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador de Renderizado
 * Genera los mockups combinando screenshots, templates y textos
 */
class RenderController extends Controller
{
    protected MockupRenderService $renderService;

    public function __construct(MockupRenderService $renderService)
    {
        $this->renderService = $renderService;
    }

    /**
     * Renderiza un mockup
     */
    public function render(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'screenshot_id' => 'required|exists:screenshots,id',
            'template_id' => 'required|exists:templates,id',
            'language' => 'required|string|max:10',
            'texts' => 'required|array',
            'texts.headline' => 'nullable|string|max:200',
            'texts.subheadline' => 'nullable|string|max:500',
            'texts.cta' => 'nullable|string|max:100',
            'output_format' => 'nullable|in:png,jpeg,webp',
        ]);

        $screenshot = Screenshot::findOrFail($validated['screenshot_id']);
        $template = Template::findOrFail($validated['template_id']);
        $user = $request->user();

        // Verificar que el screenshot pertenece al usuario
        if ($screenshot->project->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Verificar que el template es accesible para el usuario
        if ($template->is_premium && !$user->isPremium()) {
            return response()->json([
                'message' => 'Este template requiere un plan premium.',
                'upgrade_required' => true,
            ], 403);
        }

        // Verificar compatibilidad de orientación
        if (!$template->supportsOrientation($screenshot->orientation)) {
            return response()->json([
                'message' => 'El template no es compatible con la orientación del screenshot.',
            ], 400);
        }

        try {
            // Renderizar el mockup
            $result = $this->renderService->render(
                $screenshot,
                $template,
                $validated['texts'],
                $validated['language'],
                $validated['output_format'] ?? 'png'
            );

            // Crear registro en base de datos
            $renderedImage = RenderedImage::create([
                'screenshot_id' => $screenshot->id,
                'template_id' => $template->id,
                'language' => $validated['language'],
                'texts' => $validated['texts'],
                'output_path' => $result['path'],
                'output_format' => $result['format'],
                'width' => $result['width'],
                'height' => $result['height'],
                'file_size' => $result['file_size'],
                'rendered_at' => now(),
            ]);

            return response()->json([
                'message' => 'Mockup renderizado exitosamente.',
                'rendered_image' => $renderedImage,
                'url' => $renderedImage->url,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al renderizar el mockup.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno.',
            ], 500);
        }
    }

    /**
     * Previsualiza un mockup sin guardarlo
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'screenshot_id' => 'required|exists:screenshots,id',
            'template_id' => 'required|exists:templates,id',
            'texts' => 'required|array',
        ]);

        $screenshot = Screenshot::findOrFail($validated['screenshot_id']);
        $template = Template::findOrFail($validated['template_id']);

        // Verificar que el screenshot pertenece al usuario
        if ($screenshot->project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        try {
            // Generar preview (imagen temporal en base64)
            $preview = $this->renderService->generatePreview(
                $screenshot,
                $template,
                $validated['texts']
            );

            return response()->json([
                'preview' => $preview,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar preview.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno.',
            ], 500);
        }
    }

    /**
     * Lista las imágenes renderizadas de un screenshot
     */
    public function history(Request $request, Screenshot $screenshot): JsonResponse
    {
        // Verificar que el screenshot pertenece al usuario
        if ($screenshot->project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $renders = $screenshot->renderedImages()
            ->with('template')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($renders);
    }

    /**
     * Descarga una imagen renderizada
     */
    public function download(Request $request, RenderedImage $renderedImage)
    {
        // Verificar que la imagen pertenece al usuario
        if ($renderedImage->screenshot->project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $path = Storage::disk('public')->path($renderedImage->output_path);

        if (!file_exists($path)) {
            return response()->json(['message' => 'Archivo no encontrado.'], 404);
        }

        $filename = 'mockup-' . $renderedImage->id . '.' . $renderedImage->output_format;

        return response()->download($path, $filename);
    }

    /**
     * Elimina una imagen renderizada
     */
    public function destroy(Request $request, RenderedImage $renderedImage): JsonResponse
    {
        // Verificar que la imagen pertenece al usuario
        if ($renderedImage->screenshot->project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Eliminar archivo físico
        if (Storage::disk('public')->exists($renderedImage->output_path)) {
            Storage::disk('public')->delete($renderedImage->output_path);
        }

        $renderedImage->delete();

        return response()->json([
            'message' => 'Imagen eliminada exitosamente.',
        ]);
    }
}
