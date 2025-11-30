<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador de Proyectos
 * CRUD completo para proyectos del usuario
 */
class ProjectController extends Controller
{
    /**
     * Lista todos los proyectos del usuario autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $projects = $request->user()
            ->projects()
            ->withCount('screenshots')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return response()->json($projects);
    }

    /**
     * Crea un nuevo proyecto
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Verificar límite de proyectos
        if (! $user->canCreateProject()) {
            return response()->json([
                'message' => 'Has alcanzado el límite de proyectos para tu plan.',
                'upgrade_required' => true,
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'app_type' => 'nullable|string|max:50',
        ]);

        $project = $user->projects()->create($validated);

        return response()->json([
            'message' => 'Proyecto creado exitosamente.',
            'project' => $project,
        ], 201);
    }

    /**
     * Muestra un proyecto específico con sus screenshots
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        // Verificar que el proyecto pertenece al usuario
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $project->load(['screenshots' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'screenshots.renderedImages']);

        return response()->json($project);
    }

    /**
     * Actualiza un proyecto existente
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        // Verificar que el proyecto pertenece al usuario
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'app_type' => 'nullable|string|max:50',
        ]);

        $project->update($validated);

        return response()->json([
            'message' => 'Proyecto actualizado exitosamente.',
            'project' => $project,
        ]);
    }

    /**
     * Elimina un proyecto (soft delete)
     */
    public function destroy(Request $request, Project $project): JsonResponse
    {
        // Verificar que el proyecto pertenece al usuario
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $project->delete();

        return response()->json([
            'message' => 'Proyecto eliminado exitosamente.',
        ]);
    }
}
