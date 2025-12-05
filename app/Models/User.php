<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modelo de Usuario extendido para Idogui Screen
 * Incluye roles, planes y relación con proyectos
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Atributos asignables masivamente
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'plan',
        'plan_expires_at',
    ];

    /**
     * Atributos ocultos en serialización
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts de atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'plan_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación: Un usuario tiene muchos proyectos
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Verifica si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica si el plan del usuario está activo
     */
    public function hasPlanActive(): bool
    {
        if ($this->plan === 'free') {
            return true;
        }

        return $this->plan_expires_at && $this->plan_expires_at->isFuture();
    }

    /**
     * Verifica si el usuario tiene plan premium
     */
    public function isPremium(): bool
    {
        return in_array($this->plan, ['pro', 'enterprise']) && $this->hasPlanActive();
    }

    /**
     * Obtiene el límite de proyectos según el plan
     */
    public function getProjectLimit(): int
    {
        return match ($this->plan) {
            'free' => 3,
            'pro' => 20,
            'enterprise' => PHP_INT_MAX,
            default => 3,
        };
    }

    /**
     * Verifica si puede crear más proyectos
     */
    public function canCreateProject(): bool
    {
        return $this->projects()->count() < $this->getProjectLimit();
    }
}
