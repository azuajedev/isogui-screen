<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Template;
use App\Policies\ProjectPolicy;
use App\Policies\TemplatePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Proveedor de servicios de la aplicación
 * Registra servicios y configura políticas de autorización
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Mapeo de políticas de autorización
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        Project::class => ProjectPolicy::class,
        Template::class => TemplatePolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar políticas
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
