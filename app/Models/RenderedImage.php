<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Modelo de Imagen Renderizada
 * Almacena los mockups generados combinando screenshot + template + textos
 */
class RenderedImage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos asignables masivamente
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'screenshot_id',
        'template_id',
        'language',
        'texts',
        'output_path',
        'output_format',
        'width',
        'height',
        'file_size',
        'rendered_at',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'texts' => 'array',
            'width' => 'integer',
            'height' => 'integer',
            'file_size' => 'integer',
            'rendered_at' => 'datetime',
        ];
    }

    /**
     * Relación: Una imagen renderizada pertenece a un screenshot
     */
    public function screenshot(): BelongsTo
    {
        return $this->belongsTo(Screenshot::class);
    }

    /**
     * Relación: Una imagen renderizada pertenece a un template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Obtiene la URL pública de la imagen renderizada
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->output_path);
    }

    /**
     * Obtiene la ruta completa del archivo
     */
    public function getFullPathAttribute(): string
    {
        return Storage::path($this->output_path);
    }

    /**
     * Obtiene el proyecto asociado a través del screenshot
     */
    public function getProjectAttribute()
    {
        return $this->screenshot?->project;
    }

    /**
     * Obtiene el tamaño del archivo formateado
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    /**
     * Obtiene las dimensiones formateadas
     */
    public function getDimensionsAttribute(): string
    {
        return "{$this->width} x {$this->height}";
    }
}
