<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Proyecto
 * Agrupa los screenshots de una aplicación
 */
class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos asignables masivamente
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'app_type',
    ];

    /**
     * Relación: Un proyecto pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Un proyecto tiene muchos screenshots
     */
    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class);
    }

    /**
     * Obtiene los screenshots verticales
     */
    public function verticalScreenshots(): HasMany
    {
        return $this->screenshots()->where('orientation', 'vertical');
    }

    /**
     * Obtiene los screenshots horizontales
     */
    public function horizontalScreenshots(): HasMany
    {
        return $this->screenshots()->where('orientation', 'horizontal');
    }

    /**
     * Cuenta total de imágenes renderizadas del proyecto
     */
    public function getTotalRenderedImagesAttribute(): int
    {
        return $this->screenshots->sum(function ($screenshot) {
            return $screenshot->renderedImages->count();
        });
    }
}
