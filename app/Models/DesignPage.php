<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignPage extends Model
{
    protected $fillable = [
        'design_id',
        'name',
        'order',
        'canvas_type',
        'canvas_config',
        'canvas_images',
        'texts',
        'thumbnail_path',
    ];

    protected $casts = [
        'canvas_config' => 'array',
        'canvas_images' => 'array',
        'texts' => 'array',
        'order' => 'integer',
    ];

    /**
     * Relación: Una página pertenece a un diseño
     */
    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }
}
