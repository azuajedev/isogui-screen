<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Modelo de Template
 * Define las plantillas disponibles para mockups
 */
class Template extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos asignables masivamente
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'orientation',
        'description',
        'thumbnail_path',
        'layout_config',
        'is_active',
        'is_premium',
        'sort_order',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'layout_config' => 'array',
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Boot del modelo - genera slug automáticamente
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    /**
     * Relación: Un template tiene muchas imágenes renderizadas
     */
    public function renderedImages(): HasMany
    {
        return $this->hasMany(RenderedImage::class);
    }

    /**
     * Scope: Templates activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Templates gratuitos
     */
    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Scope: Templates premium
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope: Templates por orientación
     */
    public function scopeForOrientation($query, string $orientation)
    {
        return $query->where(function ($q) use ($orientation) {
            $q->where('orientation', $orientation)
                ->orWhere('orientation', 'both');
        });
    }

    /**
     * Verifica si el template es compatible con una orientación
     */
    public function supportsOrientation(string $orientation): bool
    {
        return $this->orientation === 'both' || $this->orientation === $orientation;
    }

    /**
     * Obtiene la configuración de un área específica
     */
    public function getAreaConfig(string $areaName): ?array
    {
        return $this->layout_config['areas'][$areaName] ?? null;
    }
}
