<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            // Si es petición API, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acceso denegado. Se requieren permisos de administrador.',
                ], 403);
            }
            
            // Si es petición web, redirigir al dashboard con error
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de administrador.');
        }

        return $next($request);
    }
}
