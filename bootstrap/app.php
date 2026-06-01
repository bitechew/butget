<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Deteksi otomatis base path yang kokoh dan anti-error untuk Vercel & Laragon
$basePath = (str_contains(__DIR__, 'api') || isset($_SERVER['VERCEL_ENV']))
            ? '/var/task/user'
            : dirname(__DIR__);

return Application::configure(basePath: $basePath)
    ->withRouting(
// ... sisa kode routing dan middleware di bawahnya biarkan tetap sama seperti aslinya