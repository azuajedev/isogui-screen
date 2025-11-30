<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy de Template
 * Define las autorizaciones para operaciones sobre templates
 */
class TemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede ver la lista de templates
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver un template específico
     */
    public function view(User $user, Template $template): bool
    {
        // Si el template no está activo, solo admins pueden verlo
        if (!$template->is_active) {
            return $user->isAdmin();
        }

        return true;
    }

    /**
     * Determina si el usuario puede usar un template
     */
    public function use(User $user, Template $template): bool
    {
        // Template debe estar activo
        if (!$template->is_active) {
            return false;
        }

        // Si es premium, el usuario debe tener plan premium
        if ($template->is_premium) {
            return $user->isPremium();
        }

        return true;
    }

    /**
     * Determina si el usuario puede crear templates
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determina si el usuario puede actualizar un template
     */
    public function update(User $user, Template $template): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determina si el usuario puede eliminar un template
     */
    public function delete(User $user, Template $template): bool
    {
        return $user->isAdmin();
    }
}
