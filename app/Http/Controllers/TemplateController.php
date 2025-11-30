<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador de Templates
 * Lista y gestiona las plantillas de mockups
 */
class TemplateController extends Controller
{
    /**
     * Lista todos los templates activos
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $templates = Template::active()
            ->when($request->orientation, function ($query, $orientation) {
                $query->forOrientation($orientation);
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($template) use ($user) {
                // Marcar si el usuario puede usar este template
                $template->can_use = ! $template->is_premium || $user->isPremium();

                return $template;
            });

        return response()->json($templates);
    }

    /**
     * Muestra un template específico
     */
    public function show(Template $template): JsonResponse
    {
        if (! $template->is_active) {
            return response()->json(['message' => 'Template no disponible.'], 404);
        }

        return response()->json($template);
    }

    /**
     * Lista los tipos de templates disponibles
     */
    public function types(): JsonResponse
    {
        $types = Template::active()
            ->select('type')
            ->distinct()
            ->pluck('type');

        return response()->json($types);
    }

    /**
     * Obtiene templates compatibles con un screenshot específico
     */
    public function forScreenshot(Request $request, string $orientation): JsonResponse
    {
        $user = $request->user();

        if (! in_array($orientation, ['horizontal', 'vertical'])) {
            return response()->json(['message' => 'Orientación no válida.'], 400);
        }

        $templates = Template::active()
            ->forOrientation($orientation)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($template) use ($user) {
                $template->can_use = ! $template->is_premium || $user->isPremium();

                return $template;
            });

        return response()->json($templates);
    }
}
