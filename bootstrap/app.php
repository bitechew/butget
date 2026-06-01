<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// 1. Use the actual project root from bootstrap, which works locally and on Vercel.
$basePath = dirname(__DIR__);

// Vercel may set APP_*_CACHE env vars to empty values or to the deployment
// root directory (e.g. "/var/task/user"). That causes Laravel to treat the
// app base path as a cached file path and `require` a directory. Normalize
// these values to sensible defaults so Laravel resolves proper cache files.
$defaults = [
    'APP_CONFIG_CACHE' => 'cache/config.php',
    'APP_ROUTES_CACHE' => 'cache/routes-v7.php',
    'APP_EVENTS_CACHE' => 'cache/events.php',
    'APP_SERVICES_CACHE' => 'cache/services.php',
    'APP_PACKAGES_CACHE' => 'cache/packages.php',
];

foreach ($defaults as $key => $default) {
    $val = getenv($key);

    // Treat missing, empty, or directory values as invalid and replace them
    if ($val === false || $val === '' || (is_string($val) && is_dir($val))) {
        // Use a project-relative default so Laravel will call basePath($default).
        putenv("{$key}={$default}");
        $_ENV[$key] = $default;
        $_SERVER[$key] = $default;
    }
}

// Ensure APP_BASE_PATH isn't an invalid directory value from the platform.
// We still pass the explicit $basePath to Application::configure(), but
// setting APP_BASE_PATH avoids other code inferring a wrong base path.
if (getenv('APP_BASE_PATH') === false || getenv('APP_BASE_PATH') === '' || is_dir(getenv('APP_BASE_PATH'))) {
    putenv("APP_BASE_PATH={$basePath}");
    $_ENV['APP_BASE_PATH'] = $basePath;
    $_SERVER['APP_BASE_PATH'] = $basePath;
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