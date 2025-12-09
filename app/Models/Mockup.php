<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mockup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'filename',
        'thumbnail',
        'width',
        'height',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    /**
     * Scope para obtener solo mockups activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Incrementar contador de uso
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Obtener URL pública del mockup
     */
    public function getUrlAttribute()
    {
        return asset('storage/mockups/' . $this->filename);
    }

    /**
     * Obtener URL del thumbnail
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return asset('storage/mockups/thumbnails/' . $this->thumbnail);
        }
        return $this->url;
    }
}
