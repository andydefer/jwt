<?php

namespace Andydefer\JwtAuth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Andydefer\JwtAuth\Middleware\JwtAuthMiddleware;
// ⚠️ ne pas importer AutoUser en haut → sinon erreur si le fichier est absent

class JwtAuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/jwt.php', 'jwt');
    }

    public function boot()
    {
        if (is_dir(__DIR__ . '/../database/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        if (file_exists(__DIR__ . '/../routes/api.php')) {
            Route::prefix('jwt')->group(__DIR__ . '/../routes/api.php');
        }

        $this->app->singleton('jwt.auth', fn($app) => new JwtAuth());

        $this->app['router']->aliasMiddleware('jwt.auth', JwtAuthMiddleware::class);

        if (file_exists(__DIR__ . '/../config/jwt.php')) {
            $this->publishes([
                __DIR__ . '/../config/jwt.php' => config_path('jwt.php'),
            ], 'jwt-config');
        }

        $this->ensureJwtSecret();
        $this->ensureUserModel();
    }

    protected function ensureJwtSecret(): void
    {
        if (!empty(config('jwt.secret'))) return;

        $secret = bin2hex(random_bytes(32)); // 256 bits
        $envPath = base_path('.env');

        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (!str_contains($envContent, 'JWT_SECRET=')) {
                file_put_contents($envPath, "\nJWT_SECRET={$secret}\n", FILE_APPEND);
            }
        }

        if ($this->app->runningInConsole()) {
            $this->app['Illuminate\Contracts\Console\Kernel']->call('config:clear');
        }
    }

    protected function ensureUserModel(): void
    {
        $userModel = config('auth.providers.users.model') ?? null;

        // AutoUser n’est chargé que si nécessaire
        if (
            !$userModel ||
            !class_exists($userModel) ||
            !in_array(\Tymon\JWTAuth\Contracts\JWTSubject::class, class_implements($userModel))
        ) {
            if (class_exists(\Andydefer\JwtAuth\Models\AutoUser::class)) {
                config(['auth.providers.users.model' => \Andydefer\JwtAuth\Models\AutoUser::class]);
            }
        }
    }
}
