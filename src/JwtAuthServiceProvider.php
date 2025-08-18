<?php

namespace AndyDefer\Jwt;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class JwtAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $configPath = __DIR__ . '/../config/jwt-auth.php';

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'jwt-auth');
        }
    }

    public function boot(): void
    {
        $configPath = __DIR__ . '/../config/jwt-auth.php';

        // Publie la configuration seulement si le fichier existe
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path('jwt-auth.php'),
            ], 'config');
        }

        if ($this->app->runningInConsole()) {
            $this->publishMigrations();
        }

        $this->registerRoutes();
        $this->registerMiddleware();
    }


    protected function registerRoutes(): void
    {
        if (file_exists(__DIR__ . '/../routes/api.php')) {
            Route::group($this->routeConfiguration(), function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
            });
        }
    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => 'jwt',
            'middleware' => 'api',
        ];
    }

    protected function publishMigrations(): void
    {
        $migrationsPath = __DIR__ . '/../database/migrations/';
        if (is_dir($migrationsPath)) {
            $this->publishes([
                $migrationsPath => database_path('migrations'),
            ], 'migrations');
        }
    }

    protected function registerMiddleware(): void
    {
        if (class_exists(Middleware\JwtAuthMiddleware::class)) {
            $this->app['router']->aliasMiddleware('jwt.auth', Middleware\JwtAuthMiddleware::class);
        }
    }
}
