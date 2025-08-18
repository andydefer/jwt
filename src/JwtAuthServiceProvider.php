<?php

namespace AndyDefer\Jwt;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class JwtAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Fusionne la configuration du package avec celle de l'application
        $this->mergeConfigFrom(
            __DIR__ . '/../config/jwt-auth.php',
            'jwt-auth'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerMiddleware();
    }

    /**
     * Enregistre les routes du package
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }

    /**
     * Configure les routes
     *
     * @return array
     */
    protected function routeConfiguration(): array
    {
        return [
            'prefix' => 'jwt',
            'middleware' => 'api',
        ];
    }

    /**
     * Enregistre les migrations du package
     *
     * @return void
     */
    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Enregistre le middleware du package
     *
     * @return void
     */
    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('jwt.auth', Middleware\JwtAuthMiddleware::class);
    }
}
