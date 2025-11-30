<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

/**
 * Seeder de Templates
 * Crea los templates profesionales predefinidos
 */
class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // Template 1: Gradiente Moderno (Vertical - App Store)
            [
                'name' => 'Gradiente Moderno',
                'slug' => 'gradient-modern',
                'type' => 'app-store',
                'orientation' => 'vertical',
                'description' => 'Template moderno con gradiente de colores vibrantes. Perfecto para apps de productividad y lifestyle.',
                'is_active' => true,
                'is_premium' => false,
                'sort_order' => 1,
                'layout_config' => [
                    'canvas' => [
                        'width' => 1242,
                        'height' => 2688,
                        'background' => '#6366f1',
                        'gradient' => [
                            'start' => '#6366f1',
                            'end' => '#8b5cf6',
                            'direction' => 'vertical',
                        ],
                    ],
                    'screenshot' => [
                        'x' => 121,
                        'y' => 600,
                        'width' => 1000,
                        'height' => 1800,
                        'border_radius' => 40,
                        'shadow' => [
                            'enabled' => true,
                            'blur' => 50,
                            'offset_x' => 0,
                            'offset_y' => 20,
                            'color' => 'rgba(0, 0, 0, 0.3)',
                            'opacity' => 80,
                        ],
                    ],
                    'texts' => [
                        'headline' => [
                            'x' => 100,
                            'y' => 250,
                            'size' => 72,
                            'color' => '#ffffff',
                            'weight' => 'bold',
                            'align' => 'left',
                            'max_width' => 1000,
                        ],
                        'subheadline' => [
                            'x' => 100,
                            'y' => 380,
                            'size' => 36,
                            'color' => 'rgba(255, 255, 255, 0.85)',
                            'weight' => 'normal',
                            'align' => 'left',
                            'max_width' => 900,
                        ],
                        'cta' => [
                            'x' => 100,
                            'y' => 2550,
                            'size' => 28,
                            'color' => '#ffffff',
                            'weight' => '600',
                            'align' => 'left',
                        ],
                    ],
                ],
            ],

            // Template 2: Minimalista Blanco (Vertical - App Store)
            [
                'name' => 'Minimalista Blanco',
                'slug' => 'minimal-white',
                'type' => 'app-store',
                'orientation' => 'vertical',
                'description' => 'Diseño limpio y minimalista con fondo blanco. Ideal para apps de finanzas, salud y negocios.',
                'is_active' => true,
                'is_premium' => false,
                'sort_order' => 2,
                'layout_config' => [
                    'canvas' => [
                        'width' => 1242,
                        'height' => 2688,
                        'background' => '#ffffff',
                    ],
                    'screenshot' => [
                        'x' => 121,
                        'y' => 700,
                        'width' => 1000,
                        'height' => 1700,
                        'border_radius' => 32,
                        'shadow' => [
                            'enabled' => true,
                            'blur' => 60,
                            'offset_x' => 0,
                            'offset_y' => 15,
                            'color' => 'rgba(0, 0, 0, 0.12)',
                            'opacity' => 100,
                        ],
                    ],
                    'texts' => [
                        'headline' => [
                            'x' => 100,
                            'y' => 280,
                            'size' => 64,
                            'color' => '#1e293b',
                            'weight' => 'bold',
                            'align' => 'left',
                            'max_width' => 1000,
                        ],
                        'subheadline' => [
                            'x' => 100,
                            'y' => 400,
                            'size' => 32,
                            'color' => '#64748b',
                            'weight' => 'normal',
                            'align' => 'left',
                            'max_width' => 900,
                        ],
                        'cta' => [
                            'x' => 100,
                            'y' => 2550,
                            'size' => 24,
                            'color' => '#6366f1',
                            'weight' => '600',
                            'align' => 'left',
                        ],
                    ],
                ],
            ],

            // Template 3: Dark Mode Premium (Vertical/Both)
            [
                'name' => 'Dark Mode Premium',
                'slug' => 'dark-mode-premium',
                'type' => 'app-store',
                'orientation' => 'both',
                'description' => 'Elegante diseño oscuro con acentos de color. Perfecto para apps de entretenimiento y gaming.',
                'is_active' => true,
                'is_premium' => true,
                'sort_order' => 3,
                'layout_config' => [
                    'canvas' => [
                        'width' => 1242,
                        'height' => 2688,
                        'background' => '#0f172a',
                        'gradient' => [
                            'start' => '#0f172a',
                            'end' => '#1e293b',
                            'direction' => 'vertical',
                        ],
                    ],
                    'screenshot' => [
                        'x' => 121,
                        'y' => 550,
                        'width' => 1000,
                        'height' => 1850,
                        'border_radius' => 48,
                        'shadow' => [
                            'enabled' => true,
                            'blur' => 80,
                            'offset_x' => 0,
                            'offset_y' => 30,
                            'color' => 'rgba(99, 102, 241, 0.25)',
                            'opacity' => 100,
                        ],
                    ],
                    'texts' => [
                        'headline' => [
                            'x' => 100,
                            'y' => 220,
                            'size' => 72,
                            'color' => '#f8fafc',
                            'weight' => 'bold',
                            'align' => 'left',
                            'max_width' => 1000,
                        ],
                        'subheadline' => [
                            'x' => 100,
                            'y' => 340,
                            'size' => 36,
                            'color' => '#94a3b8',
                            'weight' => 'normal',
                            'align' => 'left',
                            'max_width' => 900,
                        ],
                        'cta' => [
                            'x' => 100,
                            'y' => 2550,
                            'size' => 28,
                            'color' => '#6366f1',
                            'weight' => '600',
                            'align' => 'left',
                        ],
                    ],
                ],
            ],

            // Template 4: Feature Hero (Horizontal - Play Store)
            [
                'name' => 'Feature Hero',
                'slug' => 'feature-hero',
                'type' => 'play-store',
                'orientation' => 'horizontal',
                'description' => 'Diseño horizontal destacado para Google Play. Muestra tu app con impacto visual.',
                'is_active' => true,
                'is_premium' => true,
                'sort_order' => 4,
                'layout_config' => [
                    'canvas' => [
                        'width' => 1024,
                        'height' => 500,
                        'background' => '#1e293b',
                        'gradient' => [
                            'start' => '#1e293b',
                            'end' => '#334155',
                            'direction' => 'horizontal',
                        ],
                    ],
                    'screenshot' => [
                        'x' => 580,
                        'y' => 30,
                        'width' => 200,
                        'height' => 440,
                        'border_radius' => 20,
                        'shadow' => [
                            'enabled' => true,
                            'blur' => 40,
                            'offset_x' => 10,
                            'offset_y' => 15,
                            'color' => 'rgba(0, 0, 0, 0.4)',
                            'opacity' => 100,
                        ],
                    ],
                    'texts' => [
                        'headline' => [
                            'x' => 50,
                            'y' => 180,
                            'size' => 48,
                            'color' => '#f8fafc',
                            'weight' => 'bold',
                            'align' => 'left',
                            'max_width' => 500,
                        ],
                        'subheadline' => [
                            'x' => 50,
                            'y' => 260,
                            'size' => 24,
                            'color' => '#94a3b8',
                            'weight' => 'normal',
                            'align' => 'left',
                            'max_width' => 450,
                        ],
                        'cta' => [
                            'x' => 50,
                            'y' => 420,
                            'size' => 20,
                            'color' => '#22c55e',
                            'weight' => '600',
                            'align' => 'left',
                            'background' => 'rgba(34, 197, 94, 0.15)',
                            'padding' => 12,
                            'border_radius' => 8,
                        ],
                    ],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            Template::create($templateData);
        }
    }
}
