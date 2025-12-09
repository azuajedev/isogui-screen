<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignController;
use App\Http\Controllers\DesignImageController;
use App\Http\Controllers\MarketingCopyController;
use App\Http\Controllers\MockupController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RenderController;
use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\TemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Idogui Screen
|--------------------------------------------------------------------------
|
| Rutas API para la aplicación de generación de mockups.
| Todas las rutas requieren autenticación excepto las públicas.
|
*/

// Ruta de verificación de autenticación
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:web');

// Rutas públicas
Route::prefix('public')->group(function () {
    // Lista de idiomas disponibles
    Route::get('/languages', [MarketingCopyController::class, 'languages']);

    // Templates públicos (preview limitado)
    Route::get('/templates', [TemplateController::class, 'index']);
});

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:web')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Proyectos (CRUD completo)
    Route::apiResource('projects', ProjectController::class);

    // Diseños (sistema de guardado de mockups)
    Route::prefix('designs')->group(function () {
        Route::get('/', [DesignController::class, 'index']);
        Route::post('/', [DesignController::class, 'store']);
        Route::get('/{id}', [DesignController::class, 'show']);
        Route::put('/{id}', [DesignController::class, 'update']);
        Route::delete('/{id}', [DesignController::class, 'destroy']);
        Route::post('/{id}/duplicate', [DesignController::class, 'duplicate']);
        
        // Páginas de diseño
        Route::prefix('{design}/pages')->group(function () {
            Route::get('/', [\App\Http\Controllers\DesignPageController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\DesignPageController::class, 'store']);
            Route::get('/{page}', [\App\Http\Controllers\DesignPageController::class, 'show']);
            Route::put('/{page}', [\App\Http\Controllers\DesignPageController::class, 'update']);
            Route::delete('/{page}', [\App\Http\Controllers\DesignPageController::class, 'destroy']);
            Route::post('/{page}/duplicate', [\App\Http\Controllers\DesignPageController::class, 'duplicate']);
            Route::put('/{page}/reorder', [\App\Http\Controllers\DesignPageController::class, 'reorder']);
        });
        
        // Imágenes de diseño (almacenamiento privado)
        Route::prefix('{design}/images')->group(function () {
            Route::post('/', [DesignImageController::class, 'store']);
            Route::get('/{filename}', [DesignImageController::class, 'show'])->name('api.designs.images.show');
            Route::delete('/{filename}', [DesignImageController::class, 'destroy']);
        });
    });

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

    // Mockups (imágenes prediseñadas compartidas)
    Route::prefix('mockups')->group(function () {
        Route::get('/', [MockupController::class, 'index']);
        Route::get('/categories', [MockupController::class, 'categories']);
        Route::get('/{id}', [MockupController::class, 'show']);
        Route::post('/{id}/usage', [MockupController::class, 'incrementUsage']);
    });

    // Rutas de administración de mockups (solo admin)
    Route::middleware('admin')->prefix('admin/mockups')->group(function () {
        Route::get('/', [MockupController::class, 'adminIndex']);
        Route::post('/', [MockupController::class, 'store']);
        Route::put('/{id}', [MockupController::class, 'update']);
        Route::post('/{id}/toggle-active', [MockupController::class, 'toggleActive']);
        Route::delete('/{id}', [MockupController::class, 'destroy']);
        Route::get('/stats', [MockupController::class, 'stats']);
    });
});

// Fallback para rutas no encontradas
Route::fallback(function () {
    return response()->json([
        'message' => 'Ruta no encontrada.',
    ], 404);
});
