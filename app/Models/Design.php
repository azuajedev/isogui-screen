<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de Diseño
 * Representa un diseño/mockup guardado por el usuario
 */
class Design extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'canvas_type',
        'canvas_config',
        'canvas_images',
        'gallery_images',
        'texts',
        'thumbnail_path',
        'last_edited_at',
        'device_configs',
        'current_device_key',
    ];

    protected $casts = [
        'canvas_config' => 'array',
        'canvas_images' => 'array',
        'gallery_images' => 'array',
        'texts' => 'array',
        'last_edited_at' => 'datetime',
        'device_configs' => 'array',
    ];

    /**
     * Relación: Un diseño pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Un diseño tiene muchas páginas
     */
    public function pages(): HasMany
    {
        return $this->hasMany(DesignPage::class)->orderBy('order');
    }

    /**
     * Scope para obtener solo los diseños del usuario autenticado
     */
    public function scopeOwned($query)
    {
        return $query->where('user_id', auth()->id());
    }

    /**
     * Scope para ordenar por última edición
     */
    public function scopeRecentlyEdited($query)
    {
        return $query->orderBy('last_edited_at', 'desc');
    }
}
