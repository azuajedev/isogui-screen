<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador del Dashboard
 * Muestra estadísticas y resumen para el usuario
 */
class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Estadísticas del usuario
        $stats = [
            'total_projects' => $user->projects()->count(),
            'total_screenshots' => $user->projects()
                ->withCount('screenshots')
                ->get()
                ->sum('screenshots_count'),
            'total_renders' => $user->projects()
                ->with('screenshots.renderedImages')
                ->get()
                ->flatMap->screenshots
                ->flatMap->renderedImages
                ->count(),
        ];

        // Proyectos recientes
        $recentProjects = $user->projects()
            ->withCount('screenshots')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // Templates disponibles
        $templates = Template::active()
            ->when(! $user->isPremium(), function ($query) {
                $query->free();
            })
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        return view('dashboard', compact('stats', 'recentProjects', 'templates'));
    }

    /**
     * Obtiene estadísticas del usuario en formato JSON
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'projects' => $user->projects()->count(),
            'project_limit' => $user->getProjectLimit(),
            'can_create_project' => $user->canCreateProject(),
            'plan' => $user->plan,
            'plan_active' => $user->hasPlanActive(),
            'is_premium' => $user->isPremium(),
        ]);
    }
}
