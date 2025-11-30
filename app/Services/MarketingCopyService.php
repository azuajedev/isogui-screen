<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Servicio de Generación de Textos de Marketing
 * Utiliza IA para generar headlines, subheadlines y CTAs
 */
class MarketingCopyService
{
    protected string $apiKey;

    protected string $apiEndpoint;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.ai.api_key', '');
        $this->apiEndpoint = config('services.ai.endpoint', 'https://api.openai.com/v1/chat/completions');
        $this->model = config('services.ai.model', 'gpt-3.5-turbo');
    }

    /**
     * Genera textos de marketing para App Store/Play Store
     *
     * @param  string  $appName  Nombre de la aplicación
     * @param  string  $description  Descripción de la aplicación
     * @param  string|null  $targetAudience  Audiencia objetivo
     * @param  array  $keyFeatures  Características principales
     * @param  string  $language  Idioma de salida
     * @param  string  $tone  Tono del texto
     * @param  int  $variations  Número de variaciones a generar
     * @return array Array de variaciones de textos
     */
    public function generate(
        string $appName,
        string $description,
        ?string $targetAudience,
        array $keyFeatures,
        string $language,
        string $tone = 'professional',
        int $variations = 3
    ): array {
        $languageNames = [
            'es' => 'español',
            'en' => 'English',
            'pt' => 'português',
            'fr' => 'français',
            'de' => 'Deutsch',
            'it' => 'italiano',
            'ja' => 'japonés',
            'ko' => 'coreano',
            'zh' => 'chino',
        ];

        $toneDescriptions = [
            'professional' => 'profesional y confiable',
            'casual' => 'casual y amigable',
            'exciting' => 'emocionante y dinámico',
            'minimal' => 'minimalista y directo',
        ];

        $prompt = $this->buildGenerationPrompt(
            $appName,
            $description,
            $targetAudience,
            $keyFeatures,
            $languageNames[$language] ?? $language,
            $toneDescriptions[$tone] ?? $tone,
            $variations
        );

        // Si no hay API key, retornar textos placeholder
        if (empty($this->apiKey)) {
            return $this->generatePlaceholder($appName, $variations);
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->apiEndpoint, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un experto copywriter especializado en marketing de aplicaciones móviles. Generas textos persuasivos y efectivos para screenshots de App Store y Google Play.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.8,
                    'max_tokens' => 1500,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                return $this->parseGeneratedCopies($content, $variations);
            }

            // Fallback a placeholder si hay error
            return $this->generatePlaceholder($appName, $variations);
        } catch (\Exception $e) {
            return $this->generatePlaceholder($appName, $variations);
        }
    }

    /**
     * Traduce textos a otro idioma
     *
     * @param  array  $texts  Textos a traducir
     * @param  string  $targetLanguage  Idioma objetivo
     * @return array Textos traducidos
     */
    public function translate(array $texts, string $targetLanguage): array
    {
        $languageNames = [
            'es' => 'español',
            'en' => 'English',
            'pt' => 'português',
            'fr' => 'français',
            'de' => 'Deutsch',
            'it' => 'italiano',
            'ja' => 'japonés',
            'ko' => 'coreano',
            'zh' => 'chino',
        ];

        $langName = $languageNames[$targetLanguage] ?? $targetLanguage;

        $prompt = "Traduce los siguientes textos de marketing a {$langName}. "
            .'Mantén el tono persuasivo y adapta las expresiones culturalmente. '
            ."Responde SOLO con el JSON traducido, sin explicaciones.\n\n"
            ."Textos:\n".json_encode($texts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (empty($this->apiKey)) {
            // Retornar los mismos textos si no hay API key
            return $texts;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->apiEndpoint, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un traductor experto especializado en marketing de aplicaciones.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 500,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $translated = json_decode($content, true);

                if (is_array($translated)) {
                    return $translated;
                }
            }

            return $texts;
        } catch (\Exception $e) {
            return $texts;
        }
    }

    /**
     * Construye el prompt para generación
     */
    protected function buildGenerationPrompt(
        string $appName,
        string $description,
        ?string $targetAudience,
        array $keyFeatures,
        string $language,
        string $tone,
        int $variations
    ): string {
        $featuresText = ! empty($keyFeatures)
            ? "Características principales:\n- ".implode("\n- ", $keyFeatures)
            : '';

        $audienceText = $targetAudience
            ? "Audiencia objetivo: {$targetAudience}"
            : '';

        return <<<PROMPT
Genera {$variations} variaciones de textos de marketing para screenshots de App Store/Play Store.

Aplicación: {$appName}
Descripción: {$description}
{$audienceText}
{$featuresText}

Idioma: {$language}
Tono: {$tone}

Para cada variación genera:
1. headline: Texto principal llamativo (máximo 50 caracteres)
2. subheadline: Texto secundario explicativo (máximo 100 caracteres)
3. cta: Llamada a la acción (máximo 30 caracteres)

Responde en formato JSON así:
{
  "variations": [
    {
      "headline": "...",
      "subheadline": "...",
      "cta": "..."
    }
  ]
}
PROMPT;
    }

    /**
     * Parsea la respuesta de la IA
     */
    protected function parseGeneratedCopies(string $content, int $variations): array
    {
        // Intentar encontrar JSON balanceado con la estructura esperada
        // Buscar el primer '{' y el último '}' que corresponda
        $startPos = strpos($content, '{');
        if ($startPos === false) {
            return $this->generatePlaceholder('App', $variations);
        }

        // Encontrar el cierre correspondiente
        $depth = 0;
        $endPos = $startPos;
        $len = strlen($content);

        for ($i = $startPos; $i < $len; $i++) {
            if ($content[$i] === '{') {
                $depth++;
            } elseif ($content[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    $endPos = $i;
                    break;
                }
            }
        }

        $jsonString = substr($content, $startPos, $endPos - $startPos + 1);
        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->generatePlaceholder('App', $variations);
        }

        if (isset($data['variations']) && is_array($data['variations'])) {
            return $data['variations'];
        }

        return $this->generatePlaceholder('App', $variations);
    }

    /**
     * Genera textos placeholder cuando no hay API
     */
    protected function generatePlaceholder(string $appName, int $variations): array
    {
        $templates = [
            [
                'headline' => "Descubre {$appName}",
                'subheadline' => 'La mejor experiencia a tu alcance',
                'cta' => 'Comenzar ahora',
            ],
            [
                'headline' => "{$appName} simplifica tu vida",
                'subheadline' => 'Todo lo que necesitas en una app',
                'cta' => 'Prueba gratis',
            ],
            [
                'headline' => 'Potencia tu productividad',
                'subheadline' => "{$appName} te ayuda a lograr más",
                'cta' => 'Descargar',
            ],
            [
                'headline' => 'Nuevo. Intuitivo. Potente.',
                'subheadline' => "Conoce el poder de {$appName}",
                'cta' => 'Ver más',
            ],
            [
                'headline' => 'Tu nueva app favorita',
                'subheadline' => "{$appName} revoluciona tu día a día",
                'cta' => 'Únete ahora',
            ],
        ];

        return array_slice($templates, 0, $variations);
    }
}
