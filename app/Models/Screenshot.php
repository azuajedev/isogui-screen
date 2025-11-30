<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Modelo de Screenshot
 * Representa una captura de pantalla subida por el usuario
 */
class Screenshot extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos asignables masivamente
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'original_filename',
        'stored_path',
        'orientation',
        'width',
        'height',
        'file_size',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'file_size' => 'integer',
        ];
    }

    /**
     * Relación: Un screenshot pertenece a un proyecto
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relación: Un screenshot tiene muchas imágenes renderizadas
     */
    public function renderedImages(): HasMany
    {
        return $this->hasMany(RenderedImage::class);
    }

    /**
     * Obtiene la URL pública del screenshot
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->stored_path);
    }

    /**
     * Obtiene la ruta completa del archivo
     */
    public function getFullPathAttribute(): string
    {
        return Storage::path($this->stored_path);
    }

    /**
     * Detecta la orientación basándose en las dimensiones
     */
    public static function detectOrientation(int $width, int $height): string
    {
        return $width > $height ? 'horizontal' : 'vertical';
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
}
