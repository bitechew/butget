<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Deteksi apakah aplikasi berjalan di Vercel Serverless atau Lokal (Laragon)
$basePath = isset($_ENV['VERCEL_ENV']) || isset($_SERVER['VERCEL_ENV']) 
            ? '/var/task/user' 
            : dirname(__DIR__);

return Application::configure(basePath: $basePath)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )