<?php

namespace App\Http\Controllers;

use App\Services\MarketingCopyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador de Marketing Copy
 * Genera textos de marketing usando IA
 */
class MarketingCopyController extends Controller
{
    protected MarketingCopyService $copyService;

    public function __construct(MarketingCopyService $copyService)
    {
        $this->copyService = $copyService;
    }

    /**
     * Genera textos de marketing para un mockup
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'app_description' => 'required|string|max:1000',
            'target_audience' => 'nullable|string|max:200',
            'key_features' => 'nullable|array|max:5',
            'key_features.*' => 'string|max:100',
            'language' => 'required|string|max:10',
            'tone' => 'nullable|in:professional,casual,exciting,minimal',
            'variations' => 'nullable|integer|min:1|max:5',
        ]);

        // Verificar si el usuario tiene acceso a la funcionalidad de IA
        $user = $request->user();
        if (! $user->isPremium()) {
            return response()->json([
                'message' => 'La generación de textos con IA requiere un plan premium.',
                'upgrade_required' => true,
            ], 403);
        }

        try {
            $copies = $this->copyService->generate(
                appName: $validated['app_name'],
                description: $validated['app_description'],
                targetAudience: $validated['target_audience'] ?? null,
                keyFeatures: $validated['key_features'] ?? [],
                language: $validated['language'],
                tone: $validated['tone'] ?? 'professional',
                variations: $validated['variations'] ?? 3
            );

            return response()->json([
                'message' => 'Textos generados exitosamente.',
                'copies' => $copies,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar textos.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno.',
            ], 500);
        }
    }

    /**
     * Traduce textos existentes a otro idioma
     */
    public function translate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'texts' => 'required|array',
            'texts.headline' => 'required|string|max:200',
            'texts.subheadline' => 'nullable|string|max:500',
            'texts.cta' => 'nullable|string|max:100',
            'target_language' => 'required|string|max:10',
        ]);

        $user = $request->user();
        if (! $user->isPremium()) {
            return response()->json([
                'message' => 'La traducción requiere un plan premium.',
                'upgrade_required' => true,
            ], 403);
        }

        try {
            $translated = $this->copyService->translate(
                texts: $validated['texts'],
                targetLanguage: $validated['target_language']
            );

            return response()->json([
                'message' => 'Textos traducidos exitosamente.',
                'texts' => $translated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al traducir textos.',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno.',
            ], 500);
        }
    }

    /**
     * Lista los idiomas soportados
     */
    public function languages(): JsonResponse
    {
        return response()->json([
            'languages' => [
                ['code' => 'es', 'name' => 'Español'],
                ['code' => 'en', 'name' => 'English'],
                ['code' => 'pt', 'name' => 'Português'],
                ['code' => 'fr', 'name' => 'Français'],
                ['code' => 'de', 'name' => 'Deutsch'],
                ['code' => 'it', 'name' => 'Italiano'],
                ['code' => 'ja', 'name' => '日本語'],
                ['code' => 'ko', 'name' => '한국어'],
                ['code' => 'zh', 'name' => '中文'],
            ],
        ]);
    }
}
