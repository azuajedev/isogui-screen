<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketingCopyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RenderController;
use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\TemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - IsoGUI Screen
|--------------------------------------------------------------------------
|
| Rutas API para la aplicación de generación de mockups.
| Todas las rutas requieren autenticación excepto las públicas.
|
*/

// Ruta de verificación de autenticación
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas públicas
Route::prefix('public')->group(function () {
    // Lista de idiomas disponibles
    Route::get('/languages', [MarketingCopyController::class, 'languages']);

    // Templates públicos (preview limitado)
    Route::get('/templates', [TemplateController::class, 'index']);
});

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Proyectos (CRUD completo)
    Route::apiResource('projects', ProjectController::class);

    // Screenshots (anidados en proyectos)
    Route::prefix('projects/{project}')->group(function () {
        Route::get('/screenshots', [ScreenshotController::class, 'index']);
        Route::post('/screenshots', [ScreenshotController::class, 'store']);
    });

    // Screenshots (operaciones individuales)
    Route::prefix('screenshots')->group(function () {
        Route::get('/{screenshot}', [ScreenshotController::class, 'show']);
        Route::delete('/{screenshot}', [ScreenshotController::class, 'destroy']);
    });

    // Templates
    Route::prefix('templates')->group(function () {
        Route::get('/', [TemplateController::class, 'index']);
        Route::get('/types', [TemplateController::class, 'types']);
        Route::get('/orientation/{orientation}', [TemplateController::class, 'forScreenshot']);
        Route::get('/{template}', [TemplateController::class, 'show']);
    });

    // Renderizado
    Route::prefix('render')->group(function () {
        Route::post('/', [RenderController::class, 'render']);
        Route::post('/preview', [RenderController::class, 'preview']);
        Route::get('/history/{screenshot}', [RenderController::class, 'history']);
        Route::get('/download/{renderedImage}', [RenderController::class, 'download']);
        Route::delete('/{renderedImage}', [RenderController::class, 'destroy']);
    });

    // Marketing Copy (IA)
    Route::prefix('marketing')->group(function () {
        Route::post('/generate', [MarketingCopyController::class, 'generate']);
        Route::post('/translate', [MarketingCopyController::class, 'translate']);
    });
});

// Fallback para rutas no encontradas
Route::fallback(function () {
    return response()->json([
        'message' => 'Ruta no encontrada.',
    ], 404);
});
