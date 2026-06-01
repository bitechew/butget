<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// 1. Use the actual project root from bootstrap, which works locally and on Vercel.
$basePath = dirname(__DIR__);

// Vercel may define empty or directory APP_*_CACHE values. Ignore invalid cache env vars
// so Laravel falls back to default cached paths (prevents requiring a directory).
foreach (['APP_BASE_PATH', 'APP_CONFIG_CACHE', 'APP_ROUTES_CACHE', 'APP_EVENTS_CACHE', 'APP_SERVICES_CACHE', 'APP_PACKAGES_CACHE'] as $key) {
    if (array_key_exists($key, $_SERVER) && $_SERVER[$key] === '') {
        unset($_SERVER[$key]);
    }

    if (array_key_exists($key, $__ENV) && $_ENV[$key] === '') {
        unset($_ENV[$key]);
    }

    // If the env var points to a directory (e.g. "/var/task/user"), unset it.
    if (array_key_exists($key, $_SERVER) && is_dir($_SERVER[$key])) {
        unset($_SERVER[$key]);
    }

    if (array_key_exists($key, $_ENV) && is_dir($_ENV[$key])) {
        unset($_ENV[$key]);
    }
}

return Application::configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tetap mempertahankan setelan Sanctum & API milikmu
        $middleware->statefulApi();
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();