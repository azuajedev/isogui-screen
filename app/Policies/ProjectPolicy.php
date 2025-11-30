<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy de Proyecto
 * Define las autorizaciones para operaciones sobre proyectos
 */
class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario puede ver la lista de proyectos
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver un proyecto específico
     */
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determina si el usuario puede crear proyectos
     */
    public function create(User $user): bool
    {
        return $user->canCreateProject();
    }

    /**
     * Determina si el usuario puede actualizar un proyecto
     */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determina si el usuario puede eliminar un proyecto
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determina si el usuario puede restaurar un proyecto eliminado
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /**
     * Determina si el usuario puede eliminar permanentemente un proyecto
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id && $user->isAdmin();
    }
}
